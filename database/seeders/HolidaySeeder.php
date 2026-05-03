<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Illuminate\Support\Facades\Http;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        // Mengambil tahun saat ini dan tahun depan secara otomatis
        $tahunSekarang = date('Y');
        $years = [$tahunSekarang, $tahunSekarang + 1];

        foreach ($years as $year) {
            $response = Http::get("https://libur.deno.dev/api?year={$year}");

            if ($response->successful()) {
                $holidays = $response->json();

                foreach ($holidays as $h) {
                    if (isset($h['date'])) {
                        Holiday::updateOrCreate(
                            ['holiday_date' => $h['date']],
                            ['holiday_name' => $h['name']]
                        );
                        $this->command->info("Menambahkan libur: {$h['date']} - {$h['name']}");
                    }
                }
            }
        }
    }
}