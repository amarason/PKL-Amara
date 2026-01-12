<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Internship;
use App\Models\User;

class AttendanceDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $internship = Internship::first();
        
        $admin = User::where('role_id', 'ROLE_ADMIN')->first();

        if ($internship && $admin) {
            DB::table('attendance_document')->insert([
                [
                    'document_id'   => 'DOC-001',
                    'internship_id' => $internship->internship_id, 
                    'generated_by'  => $admin->id, 
                    'period_start'  => '2026-01-01',
                    'period_end'    => '2026-01-31',
                    'file_path'     => 'documents/absensi_januari.pdf',
                    'qr_hash'       => 'QRHASH123456',
                    'generated_at'  => now(),
                ],
            ]);
        }
    }
}