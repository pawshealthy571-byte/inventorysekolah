<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class RolePermission extends Model
{
    public const PERMISSION_DASHBOARD_VIEW = 'dashboard.view';

    public const PERMISSION_DASHBOARD_OPERATIONAL = 'dashboard.operational';

    public const PERMISSION_ITEMS_VIEW = 'items.view';

    public const PERMISSION_ITEMS_MANAGE = 'items.manage';

    public const PERMISSION_STOCK_MOVEMENTS_MANAGE = 'stock-movements.manage';

    public const PERMISSION_REQUESTS_MANAGE = 'requests.manage';

    public const PERMISSION_PURCHASES_MANAGE = 'purchases.manage';

    public const PERMISSION_ASSISTANT_USE = 'assistant.use';

    public const PERMISSION_ACCOUNTS_MANAGE = 'accounts.manage';

    public const PERMISSION_ACCESS_MANAGE = 'access.manage';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role',
        'permission',
        'allowed',
    ];

    /**
     * Get the supported permission catalog.
     *
     * @return array<string, array<string, string>>
     */
    public static function definitions(): array
    {
        return [
            self::PERMISSION_DASHBOARD_VIEW => [
                'label' => 'Dashboard',
                'description' => 'Melihat ringkasan utama inventaris.',
            ],
            self::PERMISSION_DASHBOARD_OPERATIONAL => [
                'label' => 'Operasional',
                'description' => 'Melihat halaman operasional inventaris.',
            ],
            self::PERMISSION_ITEMS_VIEW => [
                'label' => 'Daftar Barang',
                'description' => 'Melihat daftar dan detail barang.',
            ],
            self::PERMISSION_ITEMS_MANAGE => [
                'label' => 'Kelola Barang',
                'description' => 'Tambah, edit, dan hapus data barang.',
            ],
            self::PERMISSION_STOCK_MOVEMENTS_MANAGE => [
                'label' => 'Mutasi Stok',
                'description' => 'Mencatat stok masuk dan keluar.',
            ],
            self::PERMISSION_REQUESTS_MANAGE => [
                'label' => 'Permintaan Barang',
                'description' => 'Membuat dan memproses permintaan barang.',
            ],
            self::PERMISSION_PURCHASES_MANAGE => [
                'label' => 'Pembelian Barang',
                'description' => 'Melihat dan mencatat pembelian barang.',
            ],
            self::PERMISSION_ASSISTANT_USE => [
                'label' => 'AI Barang',
                'description' => 'Menggunakan asisten AI untuk input barang.',
            ],
            self::PERMISSION_ACCOUNTS_MANAGE => [
                'label' => 'Akun Management',
                'description' => 'Membuat dan mengubah akun pengguna.',
            ],
            self::PERMISSION_ACCESS_MANAGE => [
                'label' => 'Akses Management',
                'description' => 'Mengatur hak akses setiap role.',
            ],
        ];
    }

    /**
     * Get the default allowed permissions for each role.
     *
     * @return array<string, array<int, string>>
     */
    public static function defaultMatrix(): array
    {
        $operationalPermissions = [
            self::PERMISSION_DASHBOARD_VIEW,
            self::PERMISSION_DASHBOARD_OPERATIONAL,
            self::PERMISSION_ITEMS_VIEW,
            self::PERMISSION_ITEMS_MANAGE,
            self::PERMISSION_STOCK_MOVEMENTS_MANAGE,
            self::PERMISSION_REQUESTS_MANAGE,
            self::PERMISSION_PURCHASES_MANAGE,
            self::PERMISSION_ASSISTANT_USE,
        ];

        $basicPermissions = [
            self::PERMISSION_DASHBOARD_VIEW,
            self::PERMISSION_ITEMS_VIEW,
            self::PERMISSION_REQUESTS_MANAGE,
        ];

        return [
            User::ROLE_USER => $basicPermissions,
            User::ROLE_GURU => $basicPermissions,
            User::ROLE_ADMIN => [
                ...$operationalPermissions,
                self::PERMISSION_ACCOUNTS_MANAGE,
            ],
            User::ROLE_SUPERADMIN => array_keys(self::definitions()),
        ];
    }

    /**
     * Determine whether a role is allowed to use a permission.
     */
    public static function allows(string $role, string $permission): bool
    {
        $fallback = in_array($permission, static::defaultMatrix()[$role] ?? [], true);

        if (! Schema::hasTable('role_permissions')) {
            return $fallback;
        }

        $record = static::query()
            ->where('role', $role)
            ->where('permission', $permission)
            ->first();

        if (! $record) {
            return $fallback;
        }

        return (bool) $record->allowed;
    }

    /**
     * Build the permission matrix for the provided roles.
     *
     * @param  array<int, string>  $roles
     * @return array<string, array<string, bool>>
     */
    public static function matrix(array $roles): array
    {
        $matrix = [];

        foreach ($roles as $role) {
            foreach (array_keys(static::definitions()) as $permission) {
                $matrix[$role][$permission] = static::allows($role, $permission);
            }
        }

        return $matrix;
    }

    /**
     * Ensure all role permission records exist with default values.
     */
    public static function seedDefaults(): void
    {
        if (! Schema::hasTable('role_permissions')) {
            return;
        }

        foreach (static::defaultMatrix() as $role => $permissions) {
            foreach (array_keys(static::definitions()) as $permission) {
                static::query()->firstOrCreate([
                    'role' => $role,
                    'permission' => $permission,
                ], [
                    'allowed' => in_array($permission, $permissions, true),
                ]);
            }
        }
    }

    /**
     * Replace the stored permissions for a role.
     *
     * @param  array<int, string>  $allowedPermissions
     */
    public static function syncRole(string $role, array $allowedPermissions): void
    {
        static::seedDefaults();

        foreach (array_keys(static::definitions()) as $permission) {
            static::query()->updateOrCreate([
                'role' => $role,
                'permission' => $permission,
            ], [
                'allowed' => in_array($permission, $allowedPermissions, true),
            ]);
        }
    }
}
