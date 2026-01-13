<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $table = 'leave_request';
    protected $primaryKey = 'leave_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'leave_id',
        'internship_id',
        'approved_by',
        'approved_at',
        'leave_date',
        'reason',
        'document_path',
        'status'
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
     * Relasi ke User (Admin yang menyetujui)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'approved_by', 'login_id');
    }
}