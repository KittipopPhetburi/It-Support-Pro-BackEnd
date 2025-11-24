<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatisfactionResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'user_id',
        'satisfaction_questionnaire_id',
        'overall_score',
        'comment',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questionnaire()
    {
        return $this->belongsTo(SatisfactionQuestionnaire::class, 'satisfaction_questionnaire_id');
    }

    public function answers()
    {
        return $this->hasMany(SatisfactionAnswer::class);
    }
}
