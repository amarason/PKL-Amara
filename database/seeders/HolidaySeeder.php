<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Illuminate\Support\Facades\Http;


class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data tahun 2025 dan 2026
        $years = [2025, 2026];

        foreach ($years as $year) {
            // Menggunakan API publik hari libur Indonesia
            $response = Http::get("https://api-harilibur.vercel.app/api?year={$year}");

            if ($response->successful()) {
                $holidays = $response->json();

                foreach ($holidays as $h) {
                    // Hanya masukkan jika itu hari libur 
                    if (isset($h['holiday_date'])) {
                        Holiday::updateOrCreate(
                            ['holiday_date' => $h['holiday_date']],
                            ['holiday_name' => $h['holiday_name']]
                        );
                    }
                }
            }
        }
    }
}
