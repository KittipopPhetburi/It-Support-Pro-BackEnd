<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcontractContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcontractor_id',
        'contract_code',
        'title',
        'description',
        'start_date',
        'end_date',
        'scope_of_work',
        'sla_description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }
}
