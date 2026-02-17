<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * OtherRequest Model - โมเดลคำขออื่นๆ (ทั่วไป/สั่งซื้อของ)
 * 
 * จัดการคำขอที่ไม่ใช่สินทรัพย์ในระบบ (เช่น ขอซื้อเม้าส์, ขอติดตั้งปลั๊กไฟ)
 * 
 * @property int $id
 * @property int $requester_id ผู้ขอ
 * @property string $title หัวข้อ
 * @property string $item_name ชื่อสิ่งของ/บริการ
 * @property string $status สถานะ
 * @property datetime $request_date วันที่ขอ
 * @property int|null $branch_id สาขา
 * @property int|null $department_id แผนก
 */
class OtherRequest extends Model
{
    use HasFactory, HasBranch;

    protected $fillable = [
        'requester_id',
        'requester_name',
        'title',
        'item_name',
        'item_type',
        'request_type',
        'quantity',
        'unit',
        'description',
        'reason',
        'category',
        'status',
        'request_date',
        'branch_id',
        'department_id',
        'department',
        'organization',
        'asset_id',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'completed_by',
        'completed_at',
        'received_at',
        'brand',
        'model',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * ผู้ขอ (Requester)
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * สาขาที่สังกัด
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * แผนกที่สังกัด
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * สินทรัพย์ที่เกี่ยวข้อง (ถ้ามี)
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}