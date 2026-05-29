<?php

namespace Tests\Feature;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_managed_account(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->post(route('settings.accounts.store'), [
                'name' => 'Petugas Gudang',
                'email' => 'petugas@example.com',
                'role' => User::ROLE_USER,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('settings.accounts'));
        $response->assertSessionHas('status', 'Akun baru berhasil dibuat.');

        $this->assertDatabaseHas('users', [
            'email' => 'petugas@example.com',
            'role' => User::ROLE_USER,
        ]);
    }

    public function test_admin_cannot_assign_superadmin_role_or_manage_access(): void
    {
        $admin = User::factory()->admin()->create();

        $accountResponse = $this
            ->from(route('settings.accounts'))
            ->actingAs($admin)
            ->post(route('settings.accounts.store'), [
                'name' => 'Calon Superadmin',
                'email' => 'superbaru@example.com',
                'role' => User::ROLE_SUPERADMIN,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $accountResponse->assertRedirect(route('settings.accounts'));
        $accountResponse->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'superbaru@example.com',
        ]);

        $accessResponse = $this
            ->actingAs($admin)
            ->put(route('settings.access.update'), [
                'permissions' => [],
            ]);

        $accessResponse->assertForbidden();
    }

    public function test_regular_user_cannot_manage_accounts(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('settings.accounts.store'), [
                'name' => 'Tidak Boleh',
                'email' => 'forbidden@example.com',
                'role' => User::ROLE_USER,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertForbidden();
    }

    public function test_admin_can_filter_managed_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'name' => 'Guru Matematika',
            'email' => 'guru.mtk@example.com',
            'role' => User::ROLE_USER,
        ]);
        User::factory()->create([
            'name' => 'Petugas TU',
            'email' => 'tu@example.com',
            'role' => User::ROLE_USER,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('settings.accounts', [
                'q' => 'Guru',
                'role' => User::ROLE_USER,
            ]));

        $response->assertOk()
            ->assertSee('Daftar Akun Terdaftar')
            ->assertSee('Guru Matematika')
            ->assertDontSee('Petugas TU');
    }

    public function test_superadmin_can_change_role_access_and_restricted_route_becomes_forbidden(): void
    {
        $superadmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();

        $defaultMatrix = RolePermission::defaultMatrix();
        $userPermissions = array_values(array_diff(
            $defaultMatrix[User::ROLE_USER],
            []
        ));
        $managedAdminPermissions = array_values(array_diff(
            $defaultMatrix[User::ROLE_ADMIN],
            [RolePermission::PERMISSION_ITEMS_MANAGE],
        ));

        $response = $this
            ->actingAs($superadmin)
            ->put(route('settings.access.update'), [
                'permissions' => [
                    User::ROLE_USER => $userPermissions,
                    User::ROLE_GURU => $defaultMatrix[User::ROLE_GURU],
                    User::ROLE_ADMIN => $managedAdminPermissions,
                ],
            ]);

        $response->assertRedirect(route('settings.access'));
        $response->assertSessionHas('status', 'Hak akses role berhasil diperbarui.');

        $this->actingAs($admin)
            ->get(route('barang.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('barang.create'))
            ->assertForbidden();
    }

    public function test_create_item_page_hides_assistant_when_role_cannot_use_it(): void
    {
        $superadmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();

        $defaultMatrix = RolePermission::defaultMatrix();
        $adminPermissions = array_values(array_diff(
            $defaultMatrix[User::ROLE_ADMIN],
            [RolePermission::PERMISSION_ASSISTANT_USE],
        ));

        $this->actingAs($superadmin)
            ->put(route('settings.access.update'), [
                'permissions' => [
                    User::ROLE_USER => $defaultMatrix[User::ROLE_USER],
                    User::ROLE_GURU => $defaultMatrix[User::ROLE_GURU],
                    User::ROLE_ADMIN => $adminPermissions,
                ],
            ])
            ->assertRedirect(route('settings.access'));

        $this->actingAs($admin)
            ->get(route('barang.create'))
            ->assertOk()
            ->assertDontSee('Tambah Barang Dengan Chat atau Suara')
            ->assertDontSee('Gemini Assistant')
            ->assertSee('Data Inventaris Baru');
    }
}
