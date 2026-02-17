<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * BorrowingHistory Model - โมเดลประวัติการยืม/คืน
 * 
 * บันทึกประวัติการยืม (Borrow), เบิก (Requisition), และคืน (Return) ทั้งหมด
 * 
 * @property int $id
 * @property int $asset_id สินทรัพย์
 * @property int $user_id ผู้ยืม/เบิก
 * @property string $action_type ประเภท (borrow/requisition/return)
 * @property datetime $action_date วันที่ทำรายการ
 * @property string $status สถานะรายการ (active, returned, overdue)
 */
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

    /**
     * สินทรัพย์
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * ผู้ยืม/คืน
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * เจ้าหน้าที่ผู้ดำเนินการ
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * คำขอที่เกี่ยวข้อง
     */
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
