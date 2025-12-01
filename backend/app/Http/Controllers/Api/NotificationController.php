<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;

class NotificationController extends BaseCrudController
{
    protected string $modelClass = Notification::class;

    protected array $validationRules = [
        'user_id' => 'required|integer|exists:users,id',
        'type' => 'required|string|max:255',
        'message' => 'required|string',
        'read' => 'nullable|boolean',
    ];
}
