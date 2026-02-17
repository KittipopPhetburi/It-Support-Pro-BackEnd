<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * IncidentTitle Model - โมเดลหัวข้อการแจ้งซ่อมมาตรฐาน
 * 
 * ใช้สำหรับกำหนด Template หัวข้อปัญหาที่พบบ่อย (Common Issues) เพื่อให้ผู้ใช้เลือกได้ง่าย
 * 
 * @property int $id
 * @property string $title ชื่อหัวข้อ
 * @property string $category หมวดหมู่
 * @property string $priority ความสำคัญเริ่มต้น
 * @property int|null $response_time เวลาตอบกลับมาตรฐาน
 * @property int|null $resolution_time เวลาแก้ไขมาตรฐาน
 * @property boolean $is_active สถานะการใช้งาน
 */
class IncidentTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'priority',
        'response_time',
        'resolution_time',
        'is_active',
    ];

    protected $casts = [
        'response_time' => 'integer',
        'resolution_time' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: เฉพาะรายการที่ Active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: กรองตามหมวดหมู่
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
