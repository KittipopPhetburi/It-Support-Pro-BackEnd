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
        'assigned_to',
        'assigned_to_email',
        'assigned_to_phone',
        'location',
        'ip_address',
        'mac_address',
        'license_key',
        'license_type',
        'purchase_date',
        'start_date',
        'warranty_expiry',
        'expiry_date',
        'total_licenses',
        'used_licenses',
        'branch_id',
        'department_id',
        'department',
        'organization',
        'qr_code',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'start_date' => 'date',
        'warranty_expiry' => 'date',
        'expiry_date' => 'date',
        'total_licenses' => 'integer',
        'used_licenses' => 'integer',
    ];

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }
}
