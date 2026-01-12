<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Internship;
use App\Models\User;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil data internship pertama
        $internship = Internship::first();
        
        // 2. Ambil data admin pertama
        $admin = User::where('role_id', 'ROLE_ADMIN')->first();

        if ($internship && $admin) {
            DB::table('leave_request')->insert([
                [
                    'leave_id'       => 'LR-001',
                    'internship_id'  => $internship->internship_id,
                    'leave_date'     => '2026-01-09',
                    'reason'         => 'Izin sakit dengan surat dokter',
                    'document_path'  => 'uploads/leave/surat_sakit_dummy.pdf',
                    'status'         => 'menunggu',
                    'approved_by'    => $admin->login_id, 
                    'approved_at'    => Carbon::now(),
                    'created_at'     => Carbon::now(),
                    'updated_at'     => Carbon::now(),
                ],
                [
                    'leave_id'       => 'LR-002',
                    'internship_id'  => $internship->internship_id,
                    'leave_date'     => '2026-01-10',
                    'reason'         => 'Izin keperluan keluarga (tidak ada surat)',
                    'document_path'  => null,
                    'status'         => 'menunggu',
                    'approved_by'    => null,
                    'approved_at'    => null,
                    'created_at'     => Carbon::now(),
                    'updated_at'     => Carbon::now(),
                ],
            ]);
        }
    }
}