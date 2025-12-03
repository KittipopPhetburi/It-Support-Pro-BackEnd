<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatisfactionSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'rating',
        'feedback',
        'respondent_id',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function respondent()
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class, 'ticket_id', 'id');
    }
}
