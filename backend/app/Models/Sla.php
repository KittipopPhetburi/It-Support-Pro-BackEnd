<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sla extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority',
        'response_time',
        'resolution_time',
    ];

    protected $casts = [
        'response_time' => 'integer',
        'resolution_time' => 'integer',
    ];
}
