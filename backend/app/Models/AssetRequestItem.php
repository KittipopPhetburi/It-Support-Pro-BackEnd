<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_request_id',
        'asset_category_id',
        'quantity',
        'specification',
        'budget_per_item',
    ];

    protected $casts = [
        'budget_per_item' => 'decimal:2',
    ];

    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }
}
