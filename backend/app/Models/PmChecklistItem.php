<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PmChecklistItem Model - โมเดลรายการตรวจเช็ค PM
 * 
 * รายการตรวจสอบย่อยในแผนบำรุงรักษา (PM Schedule)
 * 
 * @property int $id
 * @property int $pm_schedule_id แผนการบำรุงรักษา
 * @property string $title หัวข้อตรวจสอบ
 * @property string $description รายละเอียด
 * @property boolean $is_completed สถานะทำเสร็จ
 * @property string|null $notes บันทึกผล
 * @property datetime|null $completed_at เวลาที่ทำเสร็จ
 * @property int $sort_order ลำดับ
 */
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
     * แผนการบำรุงรักษาที่รายการนี้สังกัด
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
