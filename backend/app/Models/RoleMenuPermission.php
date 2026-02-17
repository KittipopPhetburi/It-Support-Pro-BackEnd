<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RoleMenuPermission Model - โมเดลสิทธิ์การใช้งานตามบทบาท
 * 
 * กำหนดสิทธิ์ (View, Create, Update, Delete) ในแต่ละเมนูสำหรับแต่ละ Role
 * 
 * @property int $id
 * @property int $role_id บทบาท
 * @property int $menu_id เมนู
 * @property boolean $can_view สิทธิ์เข้าดู
 * @property boolean $can_create สิทธิ์เพิ่มข้อมูล
 * @property boolean $can_update สิทธิ์แก้ไข
 * @property boolean $can_delete สิทธิ์ลบ
 */
class RoleMenuPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'menu_id',
        'role_name',
        'menu_name',
        'can_view',
        'can_create',
        'can_update',
        'can_delete',
        'created_by_username',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean',
    ];

    /**
     * บทบาทเจ้าของสิทธิ์
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * เมนูที่เกี่ยวข้อง
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
