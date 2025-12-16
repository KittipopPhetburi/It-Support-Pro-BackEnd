<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetRequest extends Model
{
    use HasFactory, HasBranch;

    protected $fillable = [
        'requester_id',
        'requester_name',
        'request_type',
        'asset_type',
        'asset_id',
        'quantity',
        'borrowed_serial',
        'justification',
        'reason',
        'status',
        'request_date',
        'branch_id',
        'department_id',
        'department',
        'organization',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'reject_reason',
        'received_at',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
