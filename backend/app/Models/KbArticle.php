<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbArticle extends Model
{
    use HasFactory;

    protected $table = 'kb_articles';

    protected $fillable = [
        'title',
        'content',
        'category',
        'tags',
        'author_id',
        'created_by_id',
        'views',
        'helpful',
        'not_helpful',
    ];

    protected $casts = [
        'tags' => 'array',
        'views' => 'integer',
        'helpful' => 'integer',
        'not_helpful' => 'integer',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
