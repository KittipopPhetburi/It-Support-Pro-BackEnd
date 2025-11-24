<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
