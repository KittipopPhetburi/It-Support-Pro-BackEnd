<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbArticleRating extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'kb_article_id',
        'user_id',
        'rating_value',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(KbArticle::class, 'kb_article_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
