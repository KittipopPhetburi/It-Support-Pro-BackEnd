<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'category',
        'brand',
        'model',
        'serial_number',
        'inventory_number',
        'status',
        'assigned_to_id',
        'location',
        'purchase_date',
        'warranty_expiry',
        'branch_id',
        'department_id',
        'organization',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }
}
