<?php

namespace App\Http\Controllers;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the current user's profile settings page.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        RolePermission::seedDefaults();

        return view('profile.show', [
            'user' => $user,
            'canManageAccounts' => $user->hasPermission(RolePermission::PERMISSION_ACCOUNTS_MANAGE),
            'canManageAccess' => $user->hasPermission(RolePermission::PERMISSION_ACCESS_MANAGE),
        ]);
    }

    /**
     * Show the managed account settings page.
     */
    public function showAccounts(Request $request): View
    {
        $user = $request->user();
        $this->ensureCanManageAccounts($user);
        $filters = $request->only(['q', 'role']);
        $allManagedUsers = $this->managedUsersFor($user);
        $managedUsers = $this->managedUsersFor($user, $filters);
        $selectedManagedUser = $request->filled('edit')
            ? $allManagedUsers->firstWhere('id', $request->integer('edit'))
            : null;

        return view('profile.accounts', [
            'user' => $user,
            'managedUsers' => $managedUsers,
            'allManagedUsers' => $allManagedUsers,
            'manageableRoles' => $user->manageableRoles(),
            'roleLabels' => User::roleLabels(),
            'filters' => $filters,
            'selectedManagedUser' => $selectedManagedUser,
        ]);
    }

    /**
     * Show the access management settings page.
     */
    public function showAccess(Request $request): View
    {
        $user = $request->user();
        RolePermission::seedDefaults();

        if (! $user->hasPermission(RolePermission::PERMISSION_ACCESS_MANAGE)) {
            abort(403, 'Anda tidak memiliki akses ke pengaturan ini.');
        }

        return view('profile.access', [
            'user' => $user,
            'roleLabels' => User::roleLabels(),
            'permissionDefinitions' => RolePermission::definitions(),
            'accessRoles' => User::accessManagedRoles(),
            'rolePermissionMatrix' => RolePermission::matrix(User::accessManagedRoles()),
        ]);
    }

    /**
     * Update the current user's display name and profile photo.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'profile_photo.image' => 'Foto profil harus berupa gambar.',
            'profile_photo.mimes' => 'Foto profil harus berformat JPG, JPEG, PNG, atau WEBP.',
            'profile_photo.max' => 'Foto profil maksimal 2 MB.',
        ]);

        $user = $request->user();
        $payload = [
            'name' => $validated['name'],
        ];

        if ($request->hasFile('profile_photo')) {
            $payload['profile_photo_path'] = $this->storeProfilePhoto(
                $request->file('profile_photo'),
                $user->id,
                $user->profile_photo_path,
            );
        }

        $user->update($payload);

        return redirect()
            ->route('profile.show')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    /**
     * Update the current user's password after verifying the old one.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = $request->user();

        if (Hash::check($validated['password'], $user->password)) {
            return back()->withErrors([
                'password' => 'Password baru harus berbeda dari password lama.',
            ]);
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('profile.show')
            ->with('status', 'Password berhasil diperbarui.');
    }

    /**
     * Create a managed account from the profile settings page.
     */
    public function storeManagedUser(Request $request): RedirectResponse
    {
        $actor = $request->user();
        $this->ensureCanManageAccounts($actor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama akun wajib diisi.',
            'email.required' => 'Email akun wajib diisi.',
            'email.unique' => 'Email akun sudah dipakai.',
            'role.required' => 'Role akun wajib dipilih.',
            'password.required' => 'Password akun wajib diisi.',
            'password.min' => 'Password akun minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password akun tidak cocok.',
        ]);

        if (! $actor->canAssignRole($validated['role'])) {
            return back()->withErrors([
                'role' => 'Anda tidak dapat membuat akun dengan role tersebut.',
            ]);
        }

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('profile.accounts.show')
            ->with('status', 'Akun baru berhasil dibuat.');
    }

    /**
     * Update a managed account from the profile settings page.
     */
    public function updateManagedUser(Request $request, User $managedUser): RedirectResponse
    {
        $actor = $request->user();
        $this->ensureCanManageAccounts($actor);

        if (! $actor->canManageUser($managedUser)) {
            abort(403, 'Anda tidak dapat mengubah akun ini.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $managedUser->id],
            'role' => ['required', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama akun wajib diisi.',
            'email.required' => 'Email akun wajib diisi.',
            'email.unique' => 'Email akun sudah dipakai.',
            'role.required' => 'Role akun wajib dipilih.',
            'password.min' => 'Password reset minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password reset tidak cocok.',
        ]);

        if (! $actor->canAssignRole($validated['role'])) {
            return back()->withErrors([
                'role' => 'Anda tidak dapat menetapkan role tersebut.',
            ]);
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

        return redirect()
            ->route('profile.accounts.show')
            ->with('status', 'Akun berhasil diperbarui.');
    }

    /**
     * Update the access matrix for configurable roles.
     */
    public function updateAccess(Request $request): RedirectResponse
    {
        $actor = $request->user();

        if (! $actor->hasPermission(RolePermission::PERMISSION_ACCESS_MANAGE)) {
            abort(403, 'Anda tidak memiliki akses ke pengaturan ini.');
        }

        $input = $request->input('permissions', []);

        foreach (User::accessManagedRoles() as $role) {
            $allowedPermissions = collect($input[$role] ?? [])
                ->filter(fn (mixed $permission): bool => array_key_exists($permission, RolePermission::definitions()))
                ->values()
                ->all();

            RolePermission::syncRole($role, $allowedPermissions);
        }

        return redirect()
            ->route('profile.access.show')
            ->with('status', 'Hak akses role berhasil diperbarui.');
    }

    /**
     * Persist a new profile photo and clean up the replaced file.
     */
    private function storeProfilePhoto(UploadedFile $file, int $userId, ?string $oldPath): string
    {
        $directory = public_path('images/profiles');
        File::ensureDirectoryExists($directory);

        $filename = 'profile_'.$userId.'_'.Str::lower(Str::random(12)).'.'.$file->extension();
        $file->move($directory, $filename);

        $this->deleteProfilePhoto($oldPath);

        return 'images/profiles/'.$filename;
    }

    /**
     * Delete the previous profile photo when it belongs to the profile folder.
     */
    private function deleteProfilePhoto(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'images/profiles/')) {
            return;
        }

        $fullPath = public_path($path);

        if (is_file($fullPath)) {
            File::delete($fullPath);
        }
    }

    /**
     * Ensure the authenticated user may manage accounts.
     */
    private function ensureCanManageAccounts(User $actor): void
    {
        if (! $actor->hasPermission(RolePermission::PERMISSION_ACCOUNTS_MANAGE)) {
            abort(403, 'Anda tidak memiliki akses ke pengaturan akun.');
        }
    }

    /**
     * Load the accounts visible to the current manager.
     */
    private function managedUsersFor(User $actor, array $filters = [])
    {
        return User::query()
            ->when(! $actor->isSuperAdmin(), fn ($query) => $query->whereIn('role', $actor->manageableRoles()))
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($nestedQuery) use ($term) {
                    $nestedQuery
                        ->where('name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%');
                });
            })
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->orderByRaw("CASE role
                WHEN 'superadmin' THEN 1
                WHEN 'admin' THEN 2
                ELSE 3
            END")
            ->orderBy('name')
            ->get();
    }
}
