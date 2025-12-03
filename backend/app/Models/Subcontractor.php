<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubContractor extends Model
{
    use HasFactory;

    protected $table = 'sub_contractors';

    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'specialty',
        'province',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'status',
    ];
}
