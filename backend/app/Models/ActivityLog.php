<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ActivityLog Model - โมเดลบันทึกกิจกรรม
 * 
 * บันทึกการกระทำต่างๆ ของผู้ใช้ในระบบเพื่อการตรวจสอบ (Audit Trail)
 * 
 * @property int $id
 * @property int $user_id ผู้ใช้งานที่ทำรายการ
 * @property string $user_role บทบาท
 * @property string $action การกระทำ (Create/Update/Delete/Login/etc.)
 * @property string $module โมดูลที่เกี่ยวข้อง
 * @property string $severity ระดับความรุนแรง
 * @property datetime $timestamp เวลาที่เกิดเหตุการณ์
 * @property string|null $details รายละเอียด
 * @property string|null $ip_address IP Address
 * @property string|null $user_agent User Agent
 */
class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        // ข้อมูลพื้นฐาน
        'user_id',
        'user_role',
        'user_email',
        'action',
        'severity',
        'event_type',
        'module',
        'timestamp',
        'details',
        
        // ข้อมูลการเชื่อมต่อ
        'ip_address',
        'user_agent',
        'session_id',
        'device_type',
        'browser',
        'os',
        
        // ข้อมูลเป้าหมาย
        'target_type',
        'target_id',
        'target_name',
        
        // ข้อมูลการเปลี่ยนแปลง
        'old_value',
        'new_value',
        
        // ข้อมูล Request/Response
        'request_method',
        'request_url',
        'response_status',
        'response_time',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'response_status' => 'integer',
        'response_time' => 'integer',
    ];

    /**
     * Severity levels
     */
    const SEVERITY_INFO = 'INFO';
    const SEVERITY_WARN = 'WARN';
    const SEVERITY_ERROR = 'ERROR';
    const SEVERITY_CRITICAL = 'CRITICAL';

    /**
     * Event types
     */
    const EVENT_ACCESS = 'ACCESS';           // การเข้าถึง
    const EVENT_CHANGE = 'CHANGE';           // การเปลี่ยนแปลง
    const EVENT_AUTH = 'AUTH';               // การยืนยันตัวตน
    const EVENT_SECURITY = 'SECURITY';       // ความปลอดภัย
    const EVENT_ERROR = 'ERROR';             // ข้อผิดพลาด
    const EVENT_SYSTEM = 'SYSTEM';           // ระบบ

    /**
     * ผู้ใช้งานที่ทำรายการ
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for security-related logs
     */
    public function scopeSecurityLogs($query)
    {
        return $query->whereIn('action', ['LOGIN_FAILED', 'ACCESS_DENIED', 'LOGOUT'])
            ->orWhere('event_type', self::EVENT_SECURITY);
    }

    /**
     * Scope for error logs
     */
    public function scopeErrorLogs($query)
    {
        return $query->whereIn('severity', [self::SEVERITY_ERROR, self::SEVERITY_CRITICAL]);
    }
}
