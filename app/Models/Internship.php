<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;

class Internship extends Model
{
    use HasFactory;

    protected $table = 'internship';
    protected $primaryKey = 'internship_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'internship_id', 'user_id', 'institution_id', 'major_id', 
        'start_date', 'end_date', 'status',
    ];

    /**
     * Menghitung total hari kerja efektif (Senin-Jumat)
     * Menggunakan DB::table agar pembacaan tanggal libur akurat (String vs String)
     */
    public function getTotalSeharusnyaHadir($bulan = null, $tahun = null)
    {
        $internStart = Carbon::parse($this->start_date)->startOfDay();
        $accountCreated = Carbon::parse($this->user->created_at)->startOfDay();
        $internEnd = Carbon::parse($this->end_date)->endOfDay();
        $now = Carbon::now()->startOfDay();

        // Titik awal perhitungan
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

        // Batasi sampai hari ini saja
        $finalLimit = $now->lt($limit) ? $now : $limit;

        if ($start->gt($finalLimit)) {
            return 0;
        }

        // --- PENGAMBILAN DATA LIBUR (RAW QUERY) ---
        // Menggunakan DB::table memastikan format tanggal adalah string murni 'Y-m-d'
        $holidays = DB::table('holidays')
            ->whereBetween('holiday_date', [
                $start->format('Y-m-d'), 
                $finalLimit->format('Y-m-d')
            ])
            ->pluck('holiday_date')
            ->toArray();
        
        $totalWorkDays = 0;
        $current = $start->copy();

        while ($current->lte($finalLimit)) {
            // Format tanggal loop menjadi string 'Y-m-d' untuk pencocokan
            $currentDateString = $current->format('Y-m-d');

            // 1. Cek Weekend (Sabtu/Minggu)
            if ($current->isWeekend()) {
                $current->addDay();
                continue;
            }

            // 2. Cek Libur Nasional (String vs String)
            if (in_array($currentDateString, $holidays)) {
                $current->addDay();
                continue;
            }

            // Jika bukan Weekend dan bukan Libur, hitung kerja
            $totalWorkDays++;
            $current->addDay();
        }

        return $totalWorkDays;
    }

    // --- RELASI MODEL ---
    public function user(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'user_id', 'id'); 
    }
    
    public function major(): BelongsTo 
    { 
        return $this->belongsTo(Major::class, 'major_id', 'major_id'); 
    }
    
    public function institution(): BelongsTo 
    { 
        return $this->belongsTo(Institution::class, 'institution_id', 'institution_id'); 
    }
    
    public function attendance() 
    { 
        return $this->hasMany(Attendance::class, 'internship_id', 'internship_id'); 
    }
}