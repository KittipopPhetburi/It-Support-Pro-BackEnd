<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Notification Model - โมเดลการแจ้งเตือน
 * 
 * จัดการการแจ้งเตือนภายในระบบ (System Notifications)
 * 
 * @property int $id
 * @property int $user_id ผู้รับการแจ้งเตือน
 * @property string $type ประเภทการแจ้งเตือน
 * @property string $message ข้อความ
 * @property boolean $read สถานะการอ่าน
 */
class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'read',
    ];

    protected $casts = [
        'read' => 'boolean',
    ];

    /**
     * ผู้รับการแจ้งเตือน
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
