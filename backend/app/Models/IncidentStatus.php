<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'sort_order',
        'is_closed_state',
    ];

    protected $casts = [
        'is_closed_state' => 'boolean',
    ];

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'status_id');
    }
}
