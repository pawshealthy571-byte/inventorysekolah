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
            ->post(route('profile.accounts.store'), [
                'name' => 'Petugas Gudang',
                'email' => 'petugas@example.com',
                'role' => User::ROLE_USER,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('profile.accounts.show'));
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
            ->from(route('profile.accounts.show'))
            ->actingAs($admin)
            ->post(route('profile.accounts.store'), [
                'name' => 'Calon Superadmin',
                'email' => 'superbaru@example.com',
                'role' => User::ROLE_SUPERADMIN,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $accountResponse->assertRedirect(route('profile.accounts.show'));
        $accountResponse->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'superbaru@example.com',
        ]);

        $accessResponse = $this
            ->actingAs($admin)
            ->put(route('profile.access.update'), [
                'permissions' => [],
            ]);

        $accessResponse->assertForbidden();
    }

    public function test_regular_user_cannot_manage_accounts(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('profile.accounts.store'), [
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
            ->get(route('profile.accounts.show', [
                'q' => 'Guru',
                'role' => User::ROLE_USER,
            ]));

        $response->assertOk()
            ->assertSee('Tabel Akun')
            ->assertSee('Guru Matematika')
            ->assertDontSee('Petugas TU');
    }

    public function test_superadmin_can_change_role_access_and_restricted_route_becomes_forbidden(): void
    {
        $superadmin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();

        $defaultMatrix = RolePermission::defaultMatrix();
        $userPermissions = array_values(array_diff(
            $defaultMatrix[User::ROLE_USER],
            [RolePermission::PERMISSION_ITEMS_MANAGE],
        ));

        $response = $this
            ->actingAs($superadmin)
            ->put(route('profile.access.update'), [
                'permissions' => [
                    User::ROLE_USER => $userPermissions,
                    User::ROLE_ADMIN => $defaultMatrix[User::ROLE_ADMIN],
                ],
            ]);

        $response->assertRedirect(route('profile.access.show'));
        $response->assertSessionHas('status', 'Hak akses role berhasil diperbarui.');

        $this->actingAs($user)
            ->get(route('barang.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('barang.create'))
            ->assertForbidden();
    }

    public function test_create_item_page_hides_assistant_when_role_cannot_use_it(): void
    {
        $superadmin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();

        $defaultMatrix = RolePermission::defaultMatrix();
        $userPermissions = array_values(array_diff(
            $defaultMatrix[User::ROLE_USER],
            [RolePermission::PERMISSION_ASSISTANT_USE],
        ));

        $this->actingAs($superadmin)
            ->put(route('profile.access.update'), [
                'permissions' => [
                    User::ROLE_USER => $userPermissions,
                    User::ROLE_ADMIN => $defaultMatrix[User::ROLE_ADMIN],
                ],
            ])
            ->assertRedirect(route('profile.access.show'));

        $this->actingAs($user)
            ->get(route('barang.create'))
            ->assertOk()
            ->assertDontSee('Tambah Barang Dengan Chat atau Suara')
            ->assertSee('AI assistant belum aktif untuk akun ini.')
            ->assertSee('Data Inventaris Baru');
    }
}
