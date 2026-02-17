<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserMenuPermission Model - โมเดลสิทธิ์การใช้งานรายบุคคล
 * 
 * ใช้สำหรับ Override สิทธิ์ของ Role ในระดับผู้ใช้งานแต่ละคน (User Specific Permissions)
 * 
 * @property int $id
 * @property int $user_id ผู้ใช้งาน
 * @property int $menu_id เมนู
 * @property boolean $can_view สิทธิ์เข้าดู
 * @property boolean $can_create สิทธิ์เพิ่มข้อมูล
 * @property boolean $can_update สิทธิ์แก้ไข
 * @property boolean $can_delete สิทธิ์ลบ
 */
class UserMenuPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_id',
        'user_name',
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
     * ผู้ใช้งานเจ้าของสิทธิ์
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * เมนูที่เกี่ยวข้อง
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
