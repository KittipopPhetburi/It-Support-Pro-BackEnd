<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tax_id',
        'phone',
        'email',
        'address',
        'contact_person',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function maintenanceContracts()
    {
        return $this->hasMany(MaintenanceContract::class);
    }
}
