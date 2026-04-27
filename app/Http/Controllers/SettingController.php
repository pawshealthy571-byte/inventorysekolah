<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Setting;
use App\Models\User;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        abort_if(! $request->user()?->isAdmin() && ! $request->user()?->isSuperAdmin(), 403, 'Akses ditolak.');

        return view('settings.index', [
            'appName' => Setting::getValue('app_name', 'Sekolah Permata Harapan'),
            'appSubtitle' => Setting::getValue('app_subtitle', 'Sekolah Inventaris'),
            'logoPath' => Setting::getValue('app_logo'),
        ]);
    }

    public function update(Request $request)
    {
        abort_if(! $request->user()?->isAdmin() && ! $request->user()?->isSuperAdmin(), 403, 'Akses ditolak.');

        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_subtitle' => 'nullable|string|max:255',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        Setting::setValue('app_name', $request->app_name);
        Setting::setValue('app_subtitle', $request->app_subtitle);

        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('images', 'public');
            Setting::setValue('app_logo', $path);
        }

        return back()->with('status', 'Identitas website berhasil diperbarui!');
    }

    public function accounts(Request $request): View
    {
        $user = $request->user();
        $this->ensureCanManageAccounts($user);
        $filters = $request->only(['q', 'role']);
        $allManagedUsers = $this->managedUsersFor($user);
        $managedUsers = $this->managedUsersFor($user, $filters);
        $selectedManagedUser = $request->filled('edit')
            ? $allManagedUsers->firstWhere('id', $request->integer('edit'))
            : null;

        return view('settings.accounts', [
            'appName' => Setting::getValue('app_name', 'Sekolah Permata Harapan'),
            'user' => $user,
            'managedUsers' => $managedUsers,
            'allManagedUsers' => $allManagedUsers,
            'manageableRoles' => $user->manageableRoles(),
            'roleLabels' => User::roleLabels(),
            'filters' => $filters,
            'selectedManagedUser' => $selectedManagedUser,
        ]);
    }

    public function access(Request $request): View
    {
        $user = $request->user();
        RolePermission::seedDefaults();

        if (!$user->hasPermission(RolePermission::PERMISSION_ACCESS_MANAGE)) {
            abort(403, 'Anda tidak memiliki akses ke pengaturan ini.');
        }

        return view('settings.access', [
            'appName' => Setting::getValue('app_name', 'Sekolah Permata Harapan'),
            'user' => $user,
            'roleLabels' => User::roleLabels(),
            'permissionDefinitions' => RolePermission::definitions(),
            'accessRoles' => User::accessManagedRoles(),
            'rolePermissionMatrix' => RolePermission::matrix(User::accessManagedRoles()),
        ]);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $actor = $request->user();
        $this->ensureCanManageAccounts($actor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!$actor->canAssignRole($validated['role'])) {
            return back()->withErrors(['role' => 'Anda tidak dapat membuat akun dengan role tersebut.']);
        }

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => $validated['password'],
        ]);

        return redirect()->route('settings.accounts')->with('status', 'Akun baru berhasil dibuat.');
    }

    public function updateAccount(Request $request, User $managedUser): RedirectResponse
    {
        $actor = $request->user();
        $this->ensureCanManageAccounts($actor);

        if (!$actor->canManageUser($managedUser)) {
            abort(403, 'Anda tidak dapat mengubah akun ini.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $managedUser->id],
            'role' => ['required', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (!$actor->canAssignRole($validated['role'])) {
            return back()->withErrors(['role' => 'Anda tidak dapat menetapkan role tersebut.']);
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if ($validated['password'] ?? null) {
            $payload['password'] = $validated['password'];
        }

        $managedUser->update($payload);

        return redirect()->route('settings.accounts')->with('status', 'Akun berhasil diperbarui.');
    }

    public function updateAccess(Request $request): RedirectResponse
    {
        $actor = $request->user();

        if (!$actor->hasPermission(RolePermission::PERMISSION_ACCESS_MANAGE)) {
            abort(403, 'Anda tidak memiliki akses ke pengaturan ini.');
        }

        $input = $request->input('permissions', []);

        foreach (User::accessManagedRoles() as $role) {
            $allowedPermissions = collect($input[$role] ?? [])
                ->filter(fn(mixed $permission): bool => array_key_exists($permission, RolePermission::definitions()))
                ->values()
                ->all();

            RolePermission::syncRole($role, $allowedPermissions);
        }

        return redirect()->route('settings.access')->with('status', 'Hak akses role berhasil diperbarui.');
    }

    private function ensureCanManageAccounts(User $actor): void
    {
        if (!$actor->hasPermission(RolePermission::PERMISSION_ACCOUNTS_MANAGE)) {
            abort(403, 'Anda tidak memiliki akses ke pengaturan akun.');
        }
    }

    private function managedUsersFor(User $actor, array $filters = [])
    {
        return User::query()
            ->when(!$actor->isSuperAdmin(), fn($query) => $query->whereIn('role', $actor->manageableRoles()))
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($nestedQuery) use ($term) {
                    $nestedQuery
                        ->where('name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%');
                });
            })
            ->when($filters['role'] ?? null, fn($query, $role) => $query->where('role', $role))
            ->orderByRaw("CASE role
                WHEN 'superadmin' THEN 1
                WHEN 'admin' THEN 2
                ELSE 3
            END")
            ->orderBy('name')
            ->get();
    }
}
