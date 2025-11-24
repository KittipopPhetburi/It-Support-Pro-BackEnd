<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'sort_order',
    ];

    public function problems()
    {
        return $this->hasMany(Problem::class, 'status_id');
    }
}
