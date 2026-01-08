<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('attendance')->insert([
            [
                'attendance_id' => 'ATT001',
                'internship_id' => 'INT001',
                'attendance_date' => now()->toDateString(),
                'check_in_time' => '07:00:00',
                'check_out_time' => '16:00:00',
                'check_in_photo' => 'uploads/attendance/checkin_dummy.jpg',
                'check_out_photo' => 'uploads/attendance/checkout_dummy.jpg',
                'status' => 'hadir',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
