<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Sla Model - โมเดลข้อตกลงระดับการบริการ
 * 
 * กำหนด SLA สำหรับแต่ละ Priority (Response Time, Resolution Time)
 * 
 * @property int $id
 * @property string $name ชื่อ SLA Policy
 * @property string $priority ระดับความสำคัญ (Critical, High, Medium, Low)
 * @property int $response_time เวลาตอบกลับ (นาที)
 * @property int $resolution_time เวลาแก้ไข (นาที/ชั่วโมง)
 * @property string $description คำอธิบาย
 * @property boolean $is_active สถานะการใช้งาน
 */
class Sla extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority',
        'response_time',
        'resolution_time',
        'description',
        'is_active',
    ];

    protected $casts = [
        'response_time' => 'integer',
        'resolution_time' => 'integer',
        'is_active' => 'boolean',
    ];
}
