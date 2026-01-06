<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'province',
        'phone',
        'organization',
        'status',
        'telegram_chat_id',
        'notification_config',
    ];

    protected $casts = [
        'notification_config' => 'array',
    ];

    // ความสัมพันธ์
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class);
    }

    public function otherRequests()
    {
        return $this->hasMany(OtherRequest::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
