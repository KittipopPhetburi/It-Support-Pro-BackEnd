<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'branch_id',
        'department_id',
        'organization',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime', // ถ้ามี
    ];

    // ความสัมพันธ์กับโครงสร้างองค์กร
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // ความสัมพันธ์กับ Incident
    public function incidentsRequested()
    {
        return $this->hasMany(Incident::class, 'requester_id');
    }

    public function incidentsReported()
    {
        return $this->hasMany(Incident::class, 'reported_by_id');
    }

    public function incidentsAssigned()
    {
        return $this->hasMany(Incident::class, 'assignee_id');
    }

    // Asset ที่มอบหมายให้ user นี้ใช้
    public function assignedAssets()
    {
        return $this->hasMany(Asset::class, 'assigned_to_id');
    }

    // Problems ที่ assign ให้ user นี้
    public function assignedProblems()
    {
        return $this->hasMany(Problem::class, 'assigned_to_id');
    }

    // Requests ต่าง ๆ
    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class, 'requester_id');
    }

    public function otherRequests()
    {
        return $this->hasMany(OtherRequest::class, 'requester_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'requester_id');
    }

    // Activity Logs
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Satisfaction Surveys ตอบโดย user นี้
    public function satisfactionSurveys()
    {
        return $this->hasMany(SatisfactionSurvey::class, 'respondent_id');
    }

    // Knowledge Base
    public function authoredArticles()
    {
        return $this->hasMany(KbArticle::class, 'author_id');
    }

    public function createdArticles()
    {
        return $this->hasMany(KbArticle::class, 'created_by_id');
    }

    // Notifications
    public function notificationsCustom()
    {
        return $this->hasMany(Notification::class);
    }
}
