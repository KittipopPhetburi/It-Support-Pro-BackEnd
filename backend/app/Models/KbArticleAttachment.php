<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbArticleAttachment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'kb_article_id',
        'uploaded_by_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(KbArticle::class, 'kb_article_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
