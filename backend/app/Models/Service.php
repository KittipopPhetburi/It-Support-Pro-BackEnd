<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_category_id',
        'code',
        'name',
        'description',
        'default_priority_id',
        'default_sla_hours',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function defaultPriority()
    {
        return $this->belongsTo(IncidentPriority::class, 'default_priority_id');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }
}
