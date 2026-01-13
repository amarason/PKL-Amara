<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Internship;
use App\Models\Attendance;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GenerateAlphaAttendance extends Command
{
    protected $signature = 'attendance:generate-alpha';

    protected $description = 'Otomatis mencatat Alpha bagi peserta aktif yang tidak absen pada hari kerja';

    public function handle()
    {
        $today = Carbon::today();
        
        // 1. Lewati jika hari libur nasional (cek tabel holidays)
        $isHoliday = Holiday::whereDate('holiday_date', $today)->exists();
        if ($isHoliday) {
            $this->info('Hari ini libur nasional. Lewati pengecekan.');
            return;
        }

        // 2. Lewati jika Sabtu atau Minggu
        if ($today->isWeekend()) {
            $this->info('Hari ini akhir pekan. Lewati pengecekan.');
            return;
        }

        // 3. Ambil peserta dengan status 'aktif'
        $pesertaAktif = Internship::where('status', 'aktif')->get();
        $totalAlpha = 0;

        foreach ($pesertaAktif as $peserta) {
            // Cek apakah sudah ada catatan hari ini (hadir/izin)
            $cekAbsensi = Attendance::where('internship_id', $peserta->internship_id)
                ->whereDate('attendance_date', $today)
                ->exists();

            if (!$cekAbsensi) {
                Attendance::create([
                    'attendance_id' => 'ATT-' . strtoupper(Str::random(7)),
                    'internship_id' => $peserta->internship_id,
                    'attendance_date' => $today,
                    'status' => 'alpha',
                    'update_reason' => 'Sistem: Alpha otomatis karena tidak ada presensi hingga 23:59.'
                ]);
                $totalAlpha++;
            }
        }

        $this->info("Berhasil! Sebanyak $totalAlpha peserta dicatat Alpha.");
    }
}