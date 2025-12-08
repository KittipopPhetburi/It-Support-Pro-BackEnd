<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationNotification extends Model
{
    protected $fillable = [
        'organization_name',
        'request_type',
        'email_enabled',
        'email_recipients',
        'telegram_enabled',
        'telegram_token',
        'telegram_chat_id',
        'line_enabled',
        'line_token',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'telegram_enabled' => 'boolean',
        'line_enabled' => 'boolean',
    ];
}
