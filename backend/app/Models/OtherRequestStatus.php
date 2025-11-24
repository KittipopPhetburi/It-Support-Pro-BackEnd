<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherRequestStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'sort_order',
    ];

    public function requests()
    {
        return $this->hasMany(OtherRequest::class, 'status_id');
    }
}
