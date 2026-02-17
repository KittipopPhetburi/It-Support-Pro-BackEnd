<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PmSchedule Model - โมเดลกำหนดการ PM
 * 
 * จัดการตารางนัดหมายการบำรุงรักษา Preventive Maintenance
 * 
 * @property int $id
 * @property int $asset_id สินทรัพย์ที่ต้องบำรุงรักษา
 * @property string $frequency ความถี่ (Weekly, Monthly, Quarterly, etc.)
 * @property int|null $assigned_to ผู้รับผิดชอบ (Technician)
 * @property date $scheduled_date วันที่นัดหมาย
 * @property date $next_scheduled_date นัดหมายครั้งถัดไป
 * @property string $status สถานะ (Scheduled, Completed, Overdue, Cancelled)
 * @property string $check_result ผลการตรวจสอบ
 * @property array $issues_found ปัญหาที่พบ (JSON)
 * @property datetime|null $completed_at เวลาที่ทำเสร็จ
 * @property int|null $completed_by ผู้ที่ทำรายการเสร็จ
 */
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
     * สินทรัพย์ที่เข้า PM
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * ช่างที่ได้รับมอบหมาย
     */
    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * ผู้บันทึกการทำงานเสร็จสิ้น
     */
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * รายการ Checklist การตรวจสอบ
     */
    public function checklistItems(): HasMany
    {
        return $this->hasMany(PmChecklistItem::class)->orderBy('sort_order');
    }

    /**
     * คำนวณวันนัดหมายครั้งถัดไปตามความถี่
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
     * ตรวจสอบว่าเกินกำหนดหรือไม่
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'Completed' 
            && $this->status !== 'Cancelled' 
            && $this->scheduled_date->isPast();
    }

    /**
     * Scope: กรองตามสถานะ
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: กรองรายการที่เกินกำหนด
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'Completed')
            ->where('status', '!=', 'Cancelled')
            ->whereDate('scheduled_date', '<', now());
    }

    /**
     * Scope: รายการที่กำลังจะมาถึงในอีก $days วัน
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('status', 'Scheduled')
            ->whereBetween('scheduled_date', [now()->startOfDay(), now()->addDays($days)->endOfDay()]);
    }
}
