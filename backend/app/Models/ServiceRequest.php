<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ServiceRequest Model - โมเดลคำขอบริการ
 * 
 * จัดการคำขอใช้บริการตาม Service Catalog
 * 
 * @property int $id
 * @property int $service_id บริการที่เลือก
 * @property string $service_name ชื่อบริการ
 * @property string $description รายละเอียดเพิ่มเติม
 * @property int $requester_id ผู้ขอ
 * @property string $status สถานะ
 * @property datetime $request_date วันที่ขอ
 */
class ServiceRequest extends Model
{
    use HasFactory, HasBranch;

    protected $fillable = [
        'service_id',
        'service_name',
        'description',
        'requester_id',
        'requested_by',
        'status',
        'approved_by_id',
        'approved_at',
        'rejected_reason',
        'request_date',
        'completion_date',
        'branch_id',
        'department_id',
        'organization',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'completion_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * รายการบริการจาก Service Catalog
     */
    public function service()
    {
        return $this->belongsTo(ServiceCatalogItem::class, 'service_id');
    }

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
     * ผู้อนุมัติ
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
