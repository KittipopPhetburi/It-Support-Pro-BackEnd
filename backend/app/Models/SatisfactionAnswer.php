<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatisfactionAnswer extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'satisfaction_response_id',
        'question_id',
        'rating_value',
        'text_value',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function response()
    {
        return $this->belongsTo(SatisfactionResponse::class, 'satisfaction_response_id');
    }

    public function question()
    {
        return $this->belongsTo(SatisfactionQuestion::class, 'question_id');
    }
}
