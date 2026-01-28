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
     * Menghitung total hari kerja efektif (Senin-Jumat) dalam rentang PKL
     * dengan mempertimbangkan tanggal pembuatan akun peserta.
     */
    public function getTotalSeharusnyaHadir($bulan = null, $tahun = null)
    {
        $internStart = Carbon::parse($this->start_date)->startOfDay();
        $accountCreated = Carbon::parse($this->user->created_at)->startOfDay();
        $internEnd = Carbon::parse($this->end_date)->endOfDay();
        $now = Carbon::now()->startOfDay();

        // Titik awal perhitungan adalah mana yang lebih baru antara tanggal PKL atau pembuatan akun
        $effectiveStart = $internStart->gt($accountCreated) ? $internStart : $accountCreated;

        if ($bulan && $tahun) {
            $monthStart = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $monthEnd = Carbon::create($tahun, $bulan, 1)->endOfMonth();

            $start = $effectiveStart->gt($monthStart) ? $effectiveStart : $monthStart;
            $limit = $internEnd->lt($monthEnd) ? $internEnd : $monthEnd;
        } else {
            $start = $effectiveStart;
            $limit = $internEnd;
        }

        $finalLimit = $now->lt($limit) ? $now : $limit;

        if ($start->gt($finalLimit)) {
            return 0;
        }

        $holidays = Holiday::whereBetween('holiday_date', [
            $start->toDateString(), 
            $finalLimit->toDateString()
        ])->pluck('holiday_date')->toArray();

        $totalHariEfektif = $start->diffInDaysFiltered(function (Carbon $date) use ($holidays) {
            return !$date->isWeekend() && !in_array($date->toDateString(), $holidays);
        }, $finalLimit);

        return (int) $totalHariEfektif + 1;
    }

    /**
     * Relasi ke model User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke model Major (Penting untuk menghindari RelationNotFoundException)
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'major_id', 'major_id');
    }

    /**
     * Relasi ke model Institution
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'institution_id');
    }

    /**
     * Relasi ke data kehadiran
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'internship_id', 'internship_id');
    }
}