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
        'contact_method',
        'service_id',
        'incident_category_id',
        'subcategory',
        'priority_id',
        'status_id',
        'requester_id',
        'assignee_id',
        'branch_id',
        'department_id',
        'source',
        'location_text',
        'location',
        // Asset info
        'asset_id',
        'asset_name',
        'asset_brand',
        'asset_model',
        'asset_serial_number',
        'asset_inventory_number',
        'is_custom_asset',
        'equipment_type',
        'operating_system',
        // Repair info
        'start_repair_date',
        'completion_date',
        'repair_details',
        'repair_status',
        'replacement_equipment',
        'has_additional_cost',
        'additional_cost',
        'technician_signature',
        'customer_signature',
        // Satisfaction info
        'satisfaction_rating',
        'satisfaction_comment',
        'satisfaction_date',
        // Dates
        'opened_at',
        'first_response_at',
        'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'first_response_at' => 'datetime',
        'closed_at' => 'datetime',
        'start_repair_date' => 'datetime',
        'completion_date' => 'datetime',
        'satisfaction_date' => 'datetime',
        'is_custom_asset' => 'boolean',
        'has_additional_cost' => 'boolean',
        'additional_cost' => 'decimal:2',
        'satisfaction_rating' => 'integer',
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

    public function asset()
    {
        return $this->belongsTo(Asset::class);
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
