<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Major;
use App\Services\IdGeneratorService;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $idService = new IdGeneratorService();

        $majors = [
            'Teknik Elektro',
            'Sistem Informasi',
            'Informatika',
            'Hukum'
        ];

        foreach ($majors as $name) {
            Major::create([
                'major_id' => $idService->generateMajorId(),
                'major_name' => $name,
            ]);
        }
    }
}