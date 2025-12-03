<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    // ประเภทวันหยุด
    const TYPE_PUBLIC_HOLIDAY = 'public_holiday';      // วันหยุดราชการ
    const TYPE_COMPANY_HOLIDAY = 'company_holiday';    // วันหยุดบริษัท
    const TYPE_SICK_LEAVE = 'sick_leave';              // ลาป่วย
    const TYPE_ANNUAL_LEAVE = 'annual_leave';          // ลาพักร้อน
    const TYPE_PERSONAL_LEAVE = 'personal_leave';      // ลากิจ
    const TYPE_OTHER = 'other';                        // อื่นๆ

    protected $fillable = [
        'name',
        'type',
        'description',
        'date',
        'end_date',
        'is_recurring',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'end_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the user who owns the leave (if personal leave)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get holidays that affect all users (public and company holidays)
     */
    public function scopeAffectsAll($query)
    {
        return $query->whereIn('type', ['public_holiday', 'company_holiday']);
    }

    /**
     * Scope to get holidays for a specific user (including global holidays)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('affects_all', true)
              ->orWhere('user_id', $userId);
        });
    }

    /**
     * Get holiday type label in Thai
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PUBLIC_HOLIDAY => 'วันหยุดราชการ',
            self::TYPE_COMPANY_HOLIDAY => 'วันหยุดบริษัท',
            self::TYPE_SICK_LEAVE => 'ลาป่วย',
            self::TYPE_ANNUAL_LEAVE => 'ลาพักร้อน',
            self::TYPE_PERSONAL_LEAVE => 'ลากิจ',
            self::TYPE_OTHER => 'อื่นๆ',
            default => $this->type,
        };
    }
}
