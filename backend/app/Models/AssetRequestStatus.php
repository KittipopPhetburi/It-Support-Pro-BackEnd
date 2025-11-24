<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetRequestStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'sort_order',
    ];

    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class, 'status_id');
    }
}
