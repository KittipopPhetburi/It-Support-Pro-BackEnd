<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AssetRequest Model - โมเดลคำขอเบิก/ยืมสินทรัพย์
 * 
 * จัดการคำขอ Borrow (ยืมคืน) และ Requisition (เบิกขาด)
 * 
 * @property int $id
 * @property int $requester_id ผู้ขอ
 * @property string $requester_name ชื่อผู้ขอ (Snapshot)
 * @property string $request_type ประเภท (borrow/requisition)
 * @property string $asset_type ประเภทสินทรัพย์
 * @property int|null $asset_id สินทรัพย์ที่ระบุ (กรณีเจาะจง)
 * @property int $quantity จำนวน
 * @property string|null $borrowed_serial Serial Number ที่ได้รับ (กรณี Serialized)
 * @property string|null $justification ความจำเป็น
 * @property string|null $reason เหตุผล
 * @property string $status สถานะ (Pending, Approved, Rejected, Completed, Returned, etc.)
 * @property datetime $request_date วันที่ขอ
 * @property int|null $branch_id สาขา
 * @property int|null $department_id แผนก
 * @property datetime|null $approved_at วันที่อนุมัติ
 * @property int|null $approved_by ผู้อนุมัติ
 * @property datetime|null $due_date กำหนดคืน (กรณี borrow)
 * @property datetime|null $return_date วันที่คืนจริง
 * @property boolean $is_returned คืนแล้วหรือไม่
 */
class AssetRequest extends Model
{
    use HasFactory, HasBranch;

    protected $fillable = [
        'requester_id',
        'requester_name',
        'request_type',
        'asset_type',
        'asset_id',
        'quantity',
        'borrowed_serial',
        'justification',
        'reason',
        'status',
        'request_date',
        'branch_id',
        'department_id',
        'department',
        'organization',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'reject_reason',
        'received_at',
        'due_date',
        'borrow_date',
        'is_returned',
        'return_date',
        'return_condition',
        'return_notes',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'received_at' => 'datetime',
        'due_date' => 'datetime',
        'borrow_date' => 'datetime',
        'return_date' => 'datetime',
        'is_returned' => 'boolean',
    ];

    /**
     * ผู้ขอ (Requester)
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * สินทรัพย์ที่ขอ (กรณีระบุเจาะจง หรือเมื่อได้รับการจัดสรรแล้ว)
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
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
    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Ticket ID Format (Access: ticket_id) e.g. REQ001
     */
    public function getTicketIdAttribute()
    {
        return 'REQ' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }
}
