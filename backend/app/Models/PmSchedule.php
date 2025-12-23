<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PmSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'frequency',
        'assigned_to',
        'scheduled_date',
        'next_scheduled_date',
        'status',
        'check_result',
        'notes',
        'issues_found',
        'recommendations',
        'images',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'next_scheduled_date' => 'date',
        'completed_at' => 'datetime',
        'issues_found' => 'array',
        'images' => 'array',
    ];

    /**
     * Get the asset that this PM schedule is for.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the technician assigned to this PM.
     */
    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who completed this PM.
     */
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get the checklist items for this PM.
     */
    public function checklistItems(): HasMany
    {
        return $this->hasMany(PmChecklistItem::class)->orderBy('sort_order');
    }

    /**
     * Calculate the next scheduled date based on frequency.
     */
    public function calculateNextScheduledDate(): ?string
    {
        $date = $this->scheduled_date->copy();

        switch ($this->frequency) {
            case 'Weekly':
                return $date->addWeek()->format('Y-m-d');
            case 'Monthly':
                return $date->addMonth()->format('Y-m-d');
            case 'Quarterly':
                return $date->addMonths(3)->format('Y-m-d');
            case 'Semi-Annually':
                return $date->addMonths(6)->format('Y-m-d');
            case 'Annually':
                return $date->addYear()->format('Y-m-d');
            default:
                return null;
        }
    }

    /**
     * Check if the PM is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'Completed' 
            && $this->status !== 'Cancelled' 
            && $this->scheduled_date->isPast();
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering overdue PMs.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'Completed')
            ->where('status', '!=', 'Cancelled')
            ->whereDate('scheduled_date', '<', now());
    }

    /**
     * Scope for upcoming PMs within days.
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('status', 'Scheduled')
            ->whereBetween('scheduled_date', [now()->startOfDay(), now()->addDays($days)->endOfDay()]);
    }
}
