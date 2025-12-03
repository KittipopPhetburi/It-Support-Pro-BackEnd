<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'priority',
        'response_time',
        'resolution_time',
        'is_active',
    ];

    protected $casts = [
        'response_time' => 'integer',
        'resolution_time' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
