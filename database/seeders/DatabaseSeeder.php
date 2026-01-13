<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            RoleSeeder::class,
            HolidaySeeder::class,
            InstitutionSeeder::class,
            MajorSeeder::class,
            UserSeeder::class,
            InternshipSeeder::class,
            AttendanceSeeder::class,
            LeaveRequestSeeder::class,
            AttendanceDocumentSeeder::class,
        ]);
    }
}
