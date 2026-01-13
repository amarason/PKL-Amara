<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;
    protected $table = 'holidays';

    protected $fillable = [
        'holiday_date',
        'holiday_name',
        'is_national_holiday',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_national_holiday' => 'boolean',
    ];
}