<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentPriority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'sla_hours',
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'default_priority_id');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'priority_id');
    }
}
