<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'asset_category_id',
        'asset_status_id',
        'serial_number',
        'model',
        'brand',
        'specification',
        'purchase_date',
        'purchase_price',
        'warranty_expire_date',
        'vendor_id',
        'branch_id',
        'department_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expire_date' => 'date',
        'purchase_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function status()
    {
        return $this->belongsTo(AssetStatus::class, 'asset_status_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function logs()
    {
        return $this->hasMany(AssetLog::class);
    }

    public function attachments()
    {
        return $this->hasMany(AssetAttachment::class);
    }

    public function maintenanceContracts()
    {
        return $this->belongsToMany(MaintenanceContract::class, 'asset_contracts');
    }
}
