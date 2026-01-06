<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherRequest extends Model
{
    use HasFactory, HasBranch;

    protected $fillable = [
        'requester_id',
        'requester_name',
        'title',
        'item_name',
        'item_type',
        'request_type',
        'quantity',
        'unit',
        'description',
        'reason',
        'category',
        'status',
        'request_date',
        'branch_id',
        'department_id',
        'organization',
        'asset_id',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'completed_by',
        'completed_at',
        'received_at',
        'brand',
        'model',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'received_at' => 'datetime',
    ];

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

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}