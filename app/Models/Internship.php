<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // Pastikan Carbon di-import

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
     * Menghitung total hari yang seharusnya sudah dilalui (Hari Kerja/Efektif)
     * Digunakan untuk menghitung persentase kehadiran absolut
     */
    public function getTotalSeharusnyaHadir()
    {
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        $now = Carbon::now()->startOfDay();

        // 1. Jika PKL belum mulai
        if ($now->lt($start)) {
            return 0;
        }

        // 2. Tentukan batas akhir perhitungan (Hari ini atau Tanggal Selesai PKL)
        $tanggalAkhirHitung = $now->lt($end) ? $now : $end;

        // 3. Hitung selisih hari
        // Gunakan diffInDays() jika Sabtu-Minggu tetap dihitung masuk
        // Gunakan diffInWeekdays() jika Sabtu-Minggu libur
        return (int) $start->diffInDays($tanggalAkhirHitung) + 1;
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