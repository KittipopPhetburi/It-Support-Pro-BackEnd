<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatisfactionQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'satisfaction_questionnaire_id',
        'question_text',
        'question_type',
        'sort_order',
    ];

    public function questionnaire()
    {
        return $this->belongsTo(SatisfactionQuestionnaire::class, 'satisfaction_questionnaire_id');
    }

    public function answers()
    {
        return $this->hasMany(SatisfactionAnswer::class, 'question_id');
    }
}
