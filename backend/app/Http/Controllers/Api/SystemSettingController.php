<?php

namespace App\Http\Controllers\Api;

use App\Models\SystemSetting;

class SystemSettingController extends BaseCrudController
{
    protected string $modelClass = SystemSetting::class;

    protected array $validationRules = [
        'category' => 'nullable|string|max:255',
        'key' => 'required|string|max:255',
        'value' => 'required|string',
        'description' => 'nullable|string',
    ];
}
