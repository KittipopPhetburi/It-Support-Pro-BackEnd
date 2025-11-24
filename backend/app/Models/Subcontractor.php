<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcontractor extends Model
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

    public function contracts()
    {
        return $this->hasMany(SubcontractContract::class);
    }
}
