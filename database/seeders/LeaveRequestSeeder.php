<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('leave_request')->insert([
            [
                'leave_id'       => 'LR001',
                'internship_id'  => 'INT001',
                'leave_date'     => '2026-01-09',
                'reason'         => 'Izin sakit dengan surat dokter',
                'document_path'  => 'uploads/leave/surat_sakit_dummy.pdf', // Contoh pakai dummy
                'status'         => 'disetujui',
                'approved_by'    => 'admin01', // Sesuaikan dengan login_id di UserSeeder
                'approved_at'    => Carbon::now(),
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ],
            [
                'leave_id'       => 'LR002',
                'internship_id'  => 'INT001', // Gunakan INT001 karena INT002 belum ada di InternshipSeeder
                'leave_date'     => '2026-01-10',
                'reason'         => 'Izin keperluan keluarga (tidak ada surat)',
                'document_path'  => null, // Tetap null untuk variasi data
                'status'         => 'menunggu',
                'approved_by'    => null,
                'approved_at'    => null,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ],
        ]);
    }
}
