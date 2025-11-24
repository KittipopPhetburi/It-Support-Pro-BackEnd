<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'contract_code',
        'title',
        'description',
        'start_date',
        'end_date',
        'sla_description',
        'contact_person',
        'contact_phone',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'asset_contracts');
    }
}
