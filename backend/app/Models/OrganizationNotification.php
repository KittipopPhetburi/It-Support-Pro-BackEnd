<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * OrganizationNotification Model - โมเดลการตั้งค่าการแจ้งเตือนระดับองค์กร
 * 
 * ใช้สำหรับกำหนดช่องทางการแจ้งเตือนหลัก (Email, Telegram, Line) ของแต่ละองค์กร/สาขา
 * 
 * @property int $id
 * @property string $organization_name ชื่อองค์กร
 * @property string $request_type ประเภทคำขอที่แจ้งเตือน
 * @property boolean $email_enabled เปิดใช้งาน Email
 * @property string|null $email_recipients ผู้รับ Email
 * @property boolean $telegram_enabled เปิดใช้งาน Telegram
 * @property string|null $telegram_token Telegram Bot Token
 * @property string|null $telegram_chat_id Telegram Chat ID
 * @property boolean $line_enabled เปิดใช้งาน Line Notify
 * @property string|null $line_token Line Token
 */
class OrganizationNotification extends Model
{
    protected $fillable = [
        'organization_name',
        'request_type',
        'email_enabled',
        'email_recipients',
        'telegram_enabled',
        'telegram_token',
        'telegram_chat_id',
        'line_enabled',
        'line_token',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'telegram_enabled' => 'boolean',
        'line_enabled' => 'boolean',
    ];
}
