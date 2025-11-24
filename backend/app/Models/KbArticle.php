<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'kb_category_id',
        'code',
        'title',
        'content',
        'is_published',
        'created_by_id',
        'updated_by_id',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(KbCategory::class, 'kb_category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function attachments()
    {
        return $this->hasMany(KbArticleAttachment::class);
    }

    public function ratings()
    {
        return $this->hasMany(KbArticleRating::class);
    }
}
