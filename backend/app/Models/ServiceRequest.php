<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'service_name',
        'requester_id',
        'status',
        'request_date',
        'completion_date',
        'branch_id',
        'department_id',
        'organization',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'completion_date' => 'datetime',
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
}
