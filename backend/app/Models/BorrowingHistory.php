<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowingHistory extends Model
{
    use HasFactory;

    protected $table = 'borrowing_history';

    protected $fillable = [
        'asset_id',
        'user_id',
        'user_name',
        'action_type',
        'request_id',
        'action_date',
        'expected_return_date',
        'actual_return_date',
        'notes',
        'status',
        'processed_by',
    ];

    protected $casts = [
        'action_date' => 'datetime',
        'expected_return_date' => 'datetime',
        'actual_return_date' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function request()
    {
        return $this->belongsTo(AssetRequest::class, 'request_id');
    }

    /**
     * Get action type label in Thai
     */
    public function getActionTypeLabelAttribute(): string
    {
        return match ($this->action_type) {
            'borrow' => 'ยืม',
            'requisition' => 'เบิก',
            'return' => 'คืน',
            default => $this->action_type,
        };
    }

    /**
     * Get status label in Thai
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'กำลังใช้งาน',
            'returned' => 'คืนแล้ว',
            'overdue' => 'เกินกำหนด',
            default => $this->status,
        };
    }
}
