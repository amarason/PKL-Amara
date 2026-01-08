<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    protected $table = 'major';
    protected $primaryKey = 'major_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'major_id',
        'major_name',
    ];
}
