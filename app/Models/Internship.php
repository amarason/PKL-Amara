<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Internship extends Model
{
    use HasFactory;

    protected $table = 'internship';
    protected $primaryKey = 'internship_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'internship_id', 
        'user_id', 
        'institution_id', 
        'major_id', 
        'start_date', 
        'end_date', 
        'status',
    ];

    /**
     * Menghitung total hari yang seharusnya sudah dilalui (Hari Kerja Efektif)
     * Mengeluarkan Sabtu, Minggu, dan Hari Libur Nasional dari perhitungan.
     */
    public function getTotalSeharusnyaHadir()
    {
        $start = Carbon::parse($this->start_date)->startOfDay();
        $end = Carbon::parse($this->end_date)->startOfDay();
        $now = Carbon::now()->startOfDay();

        // 1. Jika PKL belum mulai
        if ($now->lt($start)) {
            return 0;
        }

        // 2. Tentukan batas akhir perhitungan (Hari ini atau Tanggal Selesai PKL mana yang lebih dulu)
        $tanggalAkhirHitung = $now->lt($end) ? $now : $end;

        // 3. Ambil semua daftar hari libur dari database dalam rentang waktu PKL
        $holidays = Holiday::whereBetween('holiday_date', [
            $start->toDateString(), 
            $tanggalAkhirHitung->toDateString()
        ])->pluck('holiday_date')->toArray();

        /**
         * 4. Hitung selisih hari kerja secara inklusif
         * diffInDaysFiltered digunakan untuk  iterasi setiap hari dalam rentang tersebut
         * Menghitung hari yang BUKAN weekend dan BUKAN hari libur nasional
         */
        $totalHariEfektif = $start->diffInDaysFiltered(function (Carbon $date) use ($holidays) {
            $dateString = $date->toDateString();
            
            return !$date->isWeekend() && !in_array($dateString, $holidays);
        }, $tanggalAkhirHitung);

        // Tambahkan +1 karena diffInDaysFiltered tidak menghitung hari awal secara otomatis
        return (int) $totalHariEfektif + 1;
    }

    /**
     * Relasi ke User (Identitas Peserta)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke Major
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'major_id', 'major_id');
    }

    /**
     * Relasi ke Institution
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'institution_id');
    }

    /**
     * Relasi ke tabel Attendance
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'internship_id', 'internship_id');
    }
}