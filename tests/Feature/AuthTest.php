<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
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

    public function test_authenticated_user_can_update_profile_name_and_photo(): void
    {
        $user = User::factory()->create([
            'name' => 'Operator Lama',
        ]);

        $photo = UploadedFile::fake()->image('avatar.jpg', 320, 320);

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Operator Baru',
                'profile_photo' => $photo,
            ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('status', 'Profil berhasil diperbarui.');

        $user->refresh();

        $this->assertSame('Operator Baru', $user->name);
        $this->assertNotNull($user->profile_photo_path);
        $this->assertStringStartsWith('images/profiles/', $user->profile_photo_path);
        $this->assertFileExists(public_path($user->profile_photo_path));

        File::delete(public_path($user->profile_photo_path));
    }

    public function test_authenticated_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'password123',
                'password' => 'passwordBaru456',
                'password_confirmation' => 'passwordBaru456',
            ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('status', 'Password berhasil diperbarui.');

        $user->refresh();

        $this->assertTrue(Hash::check('passwordBaru456', $user->password));
    }
}
