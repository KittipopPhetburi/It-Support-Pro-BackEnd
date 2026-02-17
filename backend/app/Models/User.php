<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model - โมเดลผู้ใช้งาน
 * 
 * จัดการข้อมูลผู้ใช้งานในระบบทั้งหมด ตั้งแต่ Admin, Technician, จนถึง User ทั่วไป
 * ใช้สำหรับ Authentication (Sanctum) และ Authorization
 * 
 * @property int $id
 * @property string $name ชื่อ-นามสกุล
 * @property string $username ชื่อผู้ใช้
 * @property string $email อีเมล
 * @property string $password รหัสผ่าน (hashed)
 * @property string $role บทบาท (admin, technician, helpdesk, user, etc.)
 * @property int|null $branch_id รหัสสาขา
 * @property int|null $department_id รหัสแผนก
 * @property string|null $organization องค์กร
 * @property string|null $phone เบอร์โทรศัพท์
 * @property string $status สถานะ (Active/Inactive)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'branch_id',
        'department_id',
        'organization',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * สาขาที่สังกัด
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * แผนกที่สังกัด
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Incidents ที่ผู้ใช้เป็นคนแจ้ง (Requester)
     */
    public function incidentsRequested()
    {
        return $this->hasMany(Incident::class, 'requester_id');
    }

    /**
     * Incidents ที่ผู้ใช้เป็นคนบันทึกเข้าระบบ (Reported By)
     */
    public function incidentsReported()
    {
        return $this->hasMany(Incident::class, 'reported_by_id');
    }

    /**
     * Incidents ที่ได้รับมอบหมายให้แก้ไข (Assignee - Technicians)
     */
    public function incidentsAssigned()
    {
        return $this->hasMany(Incident::class, 'assignee_id');
    }

    /**
     * สินทรัพย์ที่ครอบครองอยู่ (Assigned Assets)
     */
    public function assignedAssets()
    {
        return $this->hasMany(Asset::class, 'assigned_to_id');
    }

    /**
     * Problems ที่ได้รับมอบหมาย
     */
    public function assignedProblems()
    {
        return $this->hasMany(Problem::class, 'assigned_to_id');
    }

    /**
     * ประวัติการขอเบิก/ยืมสินทรัพย์
     */
    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class, 'requester_id');
    }

    /**
     * ประวัติคำขออื่นๆ
     */
    public function otherRequests()
    {
        return $this->hasMany(OtherRequest::class, 'requester_id');
    }

    /**
     * ประวัติคำขอบริการ
     */
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'requester_id');
    }

    /**
     * Activity Logs ของผู้ใช้นี้
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * แบบประเมินความพึงพอใจที่ตอบกลับมา
     */
    public function satisfactionSurveys()
    {
        return $this->hasMany(SatisfactionSurvey::class, 'respondent_id');
    }

    /**
     * บทความ KB ที่เขียนโดยผู้ใช้นี้
     */
    public function authoredArticles()
    {
        return $this->hasMany(KbArticle::class, 'author_id');
    }

    /**
     * บทความ KB ที่สร้างโดยผู้ใช้นี้ (Duplicate relation name in model but keeping structure)
     */
    public function createdArticles()
    {
        return $this->hasMany(KbArticle::class, 'created_by_id');
    }

    /**
     * การตั้งค่าการแจ้งเตือนส่วนตัว
     */
    public function notificationsCustom()
    {
        return $this->hasMany(Notification::class);
    }
}