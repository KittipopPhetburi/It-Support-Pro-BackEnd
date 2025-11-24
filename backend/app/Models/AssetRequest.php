<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'requester_id',
        'approver_id',
        'branch_id',
        'department_id',
        'status_id',
        'request_type',
        'reason',
        'requested_date',
        'approved_date',
        'completed_date',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'approved_date' => 'date',
        'completed_date' => 'date',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function status()
    {
        return $this->belongsTo(AssetRequestStatus::class, 'status_id');
    }

    public function items()
    {
        return $this->hasMany(AssetRequestItem::class);
    }
}
