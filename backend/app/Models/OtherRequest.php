<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'requester_id',
        'handler_id',
        'category_id',
        'status_id',
        'branch_id',
        'department_id',
        'title',
        'description',
        'requested_date',
        'completed_date',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'completed_date' => 'date',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handler_id');
    }

    public function category()
    {
        return $this->belongsTo(OtherRequestCategory::class);
    }

    public function status()
    {
        return $this->belongsTo(OtherRequestStatus::class);
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
