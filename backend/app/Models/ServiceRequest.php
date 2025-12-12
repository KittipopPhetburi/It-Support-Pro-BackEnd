<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory, HasBranch;

    protected $fillable = [
        'service_id',
        'service_name',
        'description',
        'requester_id',
        'requested_by',
        'status',
        'approved_by_id',
        'approved_at',
        'rejected_reason',
        'request_date',
        'completion_date',
        'branch_id',
        'department_id',
        'organization',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'completion_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(ServiceCatalogItem::class, 'service_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
