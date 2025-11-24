<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHoursProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'timezone',
    ];

    public function periods()
    {
        return $this->hasMany(BusinessHoursPeriod::class);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }
}
