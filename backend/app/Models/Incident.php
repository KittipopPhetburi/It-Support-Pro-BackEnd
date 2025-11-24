<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'contact_name',
        'contact_phone',
        'service_id',
        'incident_category_id',
        'priority_id',
        'status_id',
        'requester_id',
        'assignee_id',
        'branch_id',
        'department_id',
        'source',
        'location_text',
        'opened_at',
        'first_response_at',
        'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'first_response_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function category()
    {
        return $this->belongsTo(IncidentCategory::class, 'incident_category_id');
    }

    public function priority()
    {
        return $this->belongsTo(IncidentPriority::class);
    }

    public function status()
    {
        return $this->belongsTo(IncidentStatus::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function logs()
    {
        return $this->hasMany(IncidentLog::class);
    }

    public function attachments()
    {
        return $this->hasMany(IncidentAttachment::class);
    }

    public function problems()
    {
        return $this->belongsToMany(Problem::class, 'problem_incidents');
    }
}
