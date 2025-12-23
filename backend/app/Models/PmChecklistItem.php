<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pm_schedule_id',
        'title',
        'description',
        'is_completed',
        'notes',
        'completed_at',
        'sort_order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the PM schedule that this checklist item belongs to.
     */
    public function pmSchedule(): BelongsTo
    {
        return $this->belongsTo(PmSchedule::class);
    }

    /**
     * Mark the item as completed.
     */
    public function markCompleted(?string $notes = null): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Mark the item as incomplete.
     */
    public function markIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }
}
