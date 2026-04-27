<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\RolePermission;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'guru@sekolah.test'],
            [
                'name' => 'Guru Pengajar',
                'role' => User::ROLE_GURU,
                'password' => 'password',
            ]
        );

        // Make sure permissions for the Guru role are seeded properly.
        RolePermission::seedDefaults();
    }
}
