<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * BusinessHour Model - โมเดลเวลาทำการ
 * 
 * กำหนดเวลาทำการของบริษัทในแต่ละวันของสัปดาห์ (สำหรับการคำนวณ SLA)
 * 
 * @property int $id
 * @property int $day_of_week วันในสัปดาห์ (0-6, 0=Sunday)
 * @property time $start_time เวลาเปิด
 * @property time $end_time เวลาปิด
 * @property boolean $is_working_day เป็นวันทำการหรือไม่
 */
class BusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'is_working_day',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_working_day' => 'boolean',
    ];
}
