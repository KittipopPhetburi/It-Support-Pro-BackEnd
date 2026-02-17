<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Role Model - โมเดลบทบาทผู้ใช้งาน
 * 
 * ใช้สำหรับกำหนดบทบาท (Role) ในระบบ เช่น Admin, Technician, User
 * 
 * @property int $id
 * @property string $name ชื่อบทบาท (ภาษาอังกฤษ/Code)
 * @property string $display_name ชื่อที่แสดงผล
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'display_name'];

    /**
     * สิทธิ์การใช้งานเมนูต่างๆ ของบทบาทนี้
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(RoleMenuPermission::class);
    }
}
