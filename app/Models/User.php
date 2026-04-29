<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\LogsActivity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, LogsActivity;

    public const ROLE_SUPERADMIN = 'superadmin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_USER = 'user';

    public const ROLE_GURU = 'guru';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'profile_photo_path',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine whether the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    /**
     * Determine whether the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Determine whether the user has one of the provided roles.
     *
     * @param  array<int, string>|string  $roles
     */
    public function hasRole(array|string $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role, $roles, true);
    }

    /**
     * Resolve the display label for the current role.
     */
    public function roleLabel(): string
    {
        return self::roleLabels()[$this->role] ?? ucfirst($this->role);
    }

    /**
     * Determine whether the user may access a named permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return RolePermission::allows($this->role, $permission);
    }

    /**
     * Return the roles this user can assign or manage.
     *
     * @return array<int, string>
     */
    public function manageableRoles(): array
    {
        if ($this->isSuperAdmin()) {
            return array_keys(self::roleLabels());
        }

        if ($this->isAdmin()) {
            return [
                self::ROLE_USER,
                self::ROLE_GURU,
                self::ROLE_ADMIN,
            ];
        }

        return [];
    }

    /**
     * Determine whether the user can manage the target account.
     */
    public function canManageUser(self $target): bool
    {
        if (! $this->hasPermission(RolePermission::PERMISSION_ACCOUNTS_MANAGE)) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($target->role, $this->manageableRoles(), true);
    }

    /**
     * Determine whether the user can assign the given role.
     */
    public function canAssignRole(string $role): bool
    {
        return in_array($role, $this->manageableRoles(), true);
    }

    /**
     * Resolve the best default landing page for the user.
     */
    public function accessibleHomeRoute(): string
    {
        $candidates = [
            'dashboard' => RolePermission::PERMISSION_DASHBOARD_VIEW,
            'dashboard.operational' => RolePermission::PERMISSION_DASHBOARD_OPERATIONAL,
            'barang.index' => RolePermission::PERMISSION_ITEMS_VIEW,
            'permintaan-barang.index' => RolePermission::PERMISSION_REQUESTS_MANAGE,
            'pembelian-barang.index' => RolePermission::PERMISSION_PURCHASES_MANAGE,
        ];

        foreach ($candidates as $route => $permission) {
            if ($this->hasPermission($permission)) {
                return $route;
            }
        }

        return 'profile.show';
    }

    /**
     * Get all supported role labels.
     *
     * @return array<string, string>
     */
    public static function roleLabels(): array
    {
        return [
            self::ROLE_USER => 'Pengguna',
            self::ROLE_GURU => 'Guru',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_SUPERADMIN => 'Superadmin',
        ];
    }

    /**
     * Get the roles configurable from access management.
     *
     * @return array<int, string>
     */
    public static function accessManagedRoles(): array
    {
        return [
            self::ROLE_USER,
            self::ROLE_GURU,
            self::ROLE_ADMIN,
        ];
    }

    /**
     * Resolve the public URL for the stored profile photo.
     */
    public function profilePhotoUrl(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        return asset($this->profile_photo_path);
    }

    /**
     * Build a compact initials string for avatar placeholders.
     */
    public function initials(): string
    {
        $segments = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = collect($segments)
            ->filter()
            ->take(2)
            ->map(fn (string $segment) => mb_strtoupper(mb_substr($segment, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'U';
    }
}
