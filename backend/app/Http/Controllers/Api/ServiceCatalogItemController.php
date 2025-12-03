<?php

namespace App\Http\Controllers\Api;

use App\Models\ServiceCatalogItem;

class ServiceCatalogItemController extends BaseCrudController
{
    protected string $modelClass = ServiceCatalogItem::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category' => 'nullable|string|max:255',
        'sla' => 'nullable|string|max:255',
        'cost' => 'nullable|numeric',
        'icon' => 'nullable|string|max:255',
        'estimated_time' => 'nullable|string|max:255',
    ];
}
