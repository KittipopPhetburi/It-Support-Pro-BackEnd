<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'incident_id',
        'title',
        'description',
        'repair_status',
        'technician_id',
        'technician_name',
        'start_date',
        'completion_date',
        'has_cost',
        'cost',
        'replacement_equipment',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'completion_date' => 'datetime',
        'has_cost' => 'boolean',
        'cost' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
