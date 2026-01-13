<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Internship;
use App\Models\Institution;
use App\Services\IdGeneratorService;

class InternshipSeeder extends Seeder
{
    public function run(): void
    {
        $idService = new IdGeneratorService();

        $user = User::where('login_id', 'IP26/S1/001')->first();
        $inst = Institution::first(); 
        
        if ($user && $inst) {
            // Deteksi strata berdasarkan nama instansi
            $strata = str_contains(strtoupper($inst->institution_name), 'SMK') ? 'SMK' : 'S1';

            Internship::create([
                'internship_id' => $idService->generateInternshipId($strata),
                'user_id' => $user->id,
                'institution_id' => $inst->institution_id,
                'major_id' => 'MJR-001', 
                'start_date' => '2026-01-01',
                'end_date' => '2026-03-31',
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
