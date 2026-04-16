<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Nicolas',
            'email' => 'nicolas@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'nicolas@example.com',
            'role' => User::ROLE_USER,
        ]);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@example.com',
            'password' => 'password123',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_super_admin_seeder_creates_superadmin_account(): void
    {
        $this->seed(SuperAdminSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'superadmin@inventorysekolah.test',
            'role' => User::ROLE_SUPERADMIN,
        ]);
    }
}
