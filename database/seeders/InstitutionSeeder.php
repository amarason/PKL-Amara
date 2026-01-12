<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Services\IdGeneratorService;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $idService = new IdGeneratorService();

        $institutions = [
            'UNIVERSITAS ABC',
            'UNIVERSITAS DIPONEGORO',
            'SMK NEGERI 1 SEMARANG' // Contoh untuk testing strata SMK
        ];

        foreach ($institutions as $name) {
            Institution::create([
                'institution_id' => $idService->generateInstitutionId(),
                'institution_name' => $name,
            ]);
        }
    }
}