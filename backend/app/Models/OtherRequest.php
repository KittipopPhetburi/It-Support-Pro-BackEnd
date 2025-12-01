<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'title',
        'description',
        'category',
        'status',
        'request_date',
        'branch_id',
        'department_id',
        'organization',
    ];

    protected $casts = [
        'request_date' => 'datetime',
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
}
