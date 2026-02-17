<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Menu Model - โมเดลเมนูระบบ
 * 
 * จัดการรายการเมนูของระบบ สำหรับสร้าง Sidebar และตรวจสอบสิทธิ์
 * 
 * @property int $id
 * @property string $key คีย์อ้างอิง (เช่น dashboard, asset_management)
 * @property string $name ชื่อเมนู
 * @property string $group กลุ่มเมนู
 * @property int $sort_order ลำดับการแสดงผล
 */
class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'name', 'group', 'sort_order'];

    /**
     * สิทธิ์การใช้งานที่ผูกกับเมนูนี้ (จาก RoleMenuPermission, UserMenuPermission)
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(RoleMenuPermission::class);
    }
}
