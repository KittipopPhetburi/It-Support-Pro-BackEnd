<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHoursPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_hours_profile_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function profile()
    {
        return $this->belongsTo(BusinessHoursProfile::class, 'business_hours_profile_id');
    }
}
