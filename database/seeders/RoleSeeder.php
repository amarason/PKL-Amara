<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('role')->insert([
            [
                'role_id' => 'ROLE_ADMIN',
                'role_name' => 'Admin',
            ],
            [
                'role_id' => 'ROLE_PESERTA',
                'role_name' => 'Peserta PKL',
            ],
        ]);
    }
}
