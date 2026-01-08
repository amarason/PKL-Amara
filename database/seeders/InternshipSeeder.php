<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternshipSeeder extends Seeder
{
    public function run(): void
    {
        $user = DB::table('users')->where('login_id', 'IP26/S1/001')->first();

        DB::table('internship')->insert([
            [
                'internship_id' => 'INT001',
                'user_id' => $user->id,
                'institution_id' => 'INST001',
                'major_id' => 'Tekdus',
                'start_date' => '2026-01-01',
                'end_date' => '2026-03-31',
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
