<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SystemSetting Model - โมเดลการตั้งค่าระบบ
 * 
 * เก็บการตั้งค่าต่างๆ ของระบบ (Key-Value)
 * 
 * @property int $id
 * @property string $category หมวดหมู่การตั้งค่า
 * @property string $key คีย์
 * @property string $value ค่า
 * @property string $type ประเภทข้อมูล (string/boolean/json)
 * @property string|null $description คำอธิบาย
 */
class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'key',
        'value',
        'type',
        'description',
    ];
}
