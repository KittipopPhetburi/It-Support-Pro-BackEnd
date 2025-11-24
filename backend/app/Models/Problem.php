<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'root_cause',
        'workaround',
        'permanent_fix',
        'status_id',
        'owner_id',
    ];

    public function status()
    {
        return $this->belongsTo(ProblemStatus::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function incidents()
    {
        return $this->belongsToMany(Incident::class, 'problem_incidents');
    }
}
