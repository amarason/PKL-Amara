<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'login_id' => 'admin01',
                'role_id' => 'ROLE_ADMIN',
                'name' => 'Admin Humas',
                'password' => Hash::make('admin01'), // Password = login_id
                'is_active' => 1,
                'created_at' => now(),
            ],
            [
                'login_id' => 'IP26/S1/001',
                'role_id' => 'ROLE_PESERTA',
                'name' => 'Mawar Sari',
                'password' => Hash::make('IP26/S1/001'), // Password = login_id
                'is_active' => 1,
                'created_at' => now(),
            ],
        ]);
    }
}