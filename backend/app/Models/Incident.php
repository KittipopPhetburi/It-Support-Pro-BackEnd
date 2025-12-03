<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'category',
        'subcategory',
        'requester_id',
        'reported_by_id',
        'assignee_id',
        'resolved_at',
        'closed_at',
        'branch_id',
        'department_id',
        'organization',
        'contact_method',
        'contact_phone',
        'location',
        'asset_id',
        'asset_name',
        'asset_brand',
        'asset_model',
        'asset_serial_number',
        'asset_inventory_number',
        'is_custom_asset',
        'equipment_type',
        'operating_system',
        'start_repair_date',
        'completion_date',
        'repair_details',
        'repair_status',
        'replacement_equipment',
        'has_additional_cost',
        'additional_cost',
        'technician_signature',
        'customer_signature',
        'satisfaction_rating',
        'satisfaction_comment',
        'satisfaction_date',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'start_repair_date' => 'datetime',
        'completion_date' => 'datetime',
        'satisfaction_date' => 'datetime',
        'is_custom_asset' => 'boolean',
        'has_additional_cost' => 'boolean',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function problems()
    {
        return $this->belongsToMany(Problem::class, 'problem_incident')
                    ->withTimestamps();
    }

    public function satisfactionSurvey()
    {
        return $this->hasOne(SatisfactionSurvey::class, 'ticket_id', 'id');
    }
}
