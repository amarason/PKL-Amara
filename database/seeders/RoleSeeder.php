<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['role_id' => 'ROLE_ADMIN', 'role_name' => 'Admin'],
            ['role_id' => 'ROLE_PESERTA', 'role_name' => 'Peserta PKL'],
        ];

        foreach ($roles as $role) {
            DB::table('role')->updateOrInsert(
                ['role_id' => $role['role_id']], // Kolom yang dicek
                ['role_name' => $role['role_name']] // Kolom yang diisi
            );
        }
    }
}