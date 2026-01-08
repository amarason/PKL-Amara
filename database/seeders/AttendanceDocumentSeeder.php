<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = DB::table('users')->where('login_id', 'admin01')->first();
        DB::table('attendance_document')->insert([
            [
                'document_id' => 'DOC001',
                'internship_id' => 'INT001',
                'generated_by' => $admin->id, // admin
                'period_start' => '2026-01-01',
                'period_end' => '2026-01-31',
                'file_path' => 'documents/absensi_januari.pdf',
                'qr_hash' => 'QRHASH123456',
                'generated_at' => now(),
            ],
        ]);
    }
}
