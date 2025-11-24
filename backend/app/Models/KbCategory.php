<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'description',
    ];

    public function parent()
    {
        return $this->belongsTo(KbCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(KbCategory::class, 'parent_id');
    }

    public function articles()
    {
        return $this->hasMany(KbArticle::class);
    }
}
