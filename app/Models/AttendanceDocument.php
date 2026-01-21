<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDocument extends Model
{
    protected $table = 'attendance_document';
    protected $primaryKey = 'document_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'internship_id',
        'generated_by',
        'period_start',
        'period_end',
        'file_path',
        'qr_hash',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    /**
     * Relasi ke Internship
     */
    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'internship_id');
    }

    /**
     * Relasi ke User (yang generate dokumen)
     */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by', 'id');
    }
}
