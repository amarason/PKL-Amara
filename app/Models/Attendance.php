<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'attendance_id',
        'internship_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'check_in_photo',
        'check_out_photo',
        'status',
        'updated_by',
        'update_reason'
    ];

    public $timestamps = true;

    /**
     * Relasi ke Internship
     */
    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'internship_id');
    }   

    /**
     * Relasi ke User (Admin yang melakukan koreksi/update)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}