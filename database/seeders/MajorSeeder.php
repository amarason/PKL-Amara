<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('major')->insert([
            [
                'major_id' => 'Tekdus',
                'major_name' => 'Teknik Industri',
            ],
            [
                'major_id' => 'SI',
                'major_name' => 'Sistem Informasi',
            ],
        ]);
    }
}
