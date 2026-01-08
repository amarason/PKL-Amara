<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Relasi ke User (Identitas Peserta)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke Major
     */
    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id', 'major_id');
    }

    /**
     * Relasi ke Institution
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'institution_id');
    }
}