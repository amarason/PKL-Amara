<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('institution')->insert([
            [
                'institution_id' => 'INST001',
                'institution_name' => 'UNIVERSITAS ABC',
            ],
        ]);
    }
}
