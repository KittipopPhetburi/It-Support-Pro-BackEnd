<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'incident_id',
        'user_id',
        'log_type',
        'old_status_id',
        'new_status_id',
        'message',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function oldStatus()
    {
        return $this->belongsTo(IncidentStatus::class, 'old_status_id');
    }

    public function newStatus()
    {
        return $this->belongsTo(IncidentStatus::class, 'new_status_id');
    }
}
