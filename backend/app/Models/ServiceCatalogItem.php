<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCatalogItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'sla',
        'cost',
        'icon',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
    ];

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'service_id');
    }
}
