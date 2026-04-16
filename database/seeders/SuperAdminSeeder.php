<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed the application's super admin account.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'superadmin@inventorysekolah.test',
        ], [
            'name' => 'Super Admin',
            'role' => User::ROLE_SUPERADMIN,
            'email_verified_at' => now(),
            'password' => 'superadmin123',
        ]);
    }
}
