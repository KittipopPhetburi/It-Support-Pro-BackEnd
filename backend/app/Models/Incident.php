<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Incident Model - โมเดลการแจ้งเหตุขัดข้อง
 * 
 * จัดการข้อมูล Ticket การแจ้งซ่อม/ปัญหา IT
 * 
 * @property int $id
 * @property string $title หัวข้อ
 * @property string $description รายละเอียด
 * @property string $priority ความสำคัญ (Critical, High, Medium, Low)
 * @property string $status สถานะ (Open, In Progress, Resolved, Closed, etc.)
 * @property int $requester_id ผู้แจ้ง
 * @property int|null $assignee_id ผู้รับผิดชอบ (Technician)
 * @property int|null $branch_id สาขา
 * @property int|null $asset_id สินทรัพย์ที่เกี่ยวข้อง
 * @property datetime|null $resolved_at เวลาแก้ไขเสร็จ
 * @property datetime|null $closed_at เวลาปิดงาน
 * @property datetime|null $sla_due_at กำหนดเวลาตาม SLA
 */
class Incident extends Model
{
    use HasFactory, HasBranch;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'category',
        'subcategory',
        'requester_id',
        'reported_by_id',
        'assignee_id',
        'resolved_at',
        'closed_at',
        'branch_id',
        'department_id',
        'organization',
        'contact_method',
        'contact_phone',
        'location',
        'asset_id',
        'previous_asset_status',
        'asset_name',
        'asset_brand',
        'asset_model',
        'asset_serial_number',
        'asset_inventory_number',
        'is_custom_asset',
        'equipment_type',
        'operating_system',
        'start_repair_date',
        'completion_date',
        'repair_details',
        'repair_status',
        'replacement_equipment',
        'has_additional_cost',
        'additional_cost',
        'technician_signature',
        'customer_signature',
        'satisfaction_rating',
        'satisfaction_comment',
        'satisfaction_date',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'start_repair_date' => 'datetime',
        'completion_date' => 'datetime',
        'satisfaction_date' => 'datetime',
        'is_custom_asset' => 'boolean',
        'has_additional_cost' => 'boolean',
    ];

    /**
     * ผู้แจ้งปัญหา (Requester)
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * ผู้บันทึกข้อมูลเข้าระบบ (อาจเป็น Helpdesk รับเรื่องแทน)
     */
    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    /**
     * เจ้าหน้าที่ที่รับผิดชอบงาน (Technician)
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * สินทรัพย์ที่เกี่ยวข้องกับปัญหานี้
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * สาขาที่เกิดปัญหา
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * แผนกที่เกิดปัญหา
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Problem ที่ Incident นี้เป็นส่วนหนึ่ง
     */
    public function problems()
    {
        return $this->belongsToMany(Problem::class, 'problem_incident')
                    ->withTimestamps();
    }

    /**
     * แบบประเมินความพึงพอใจ
     */
    public function satisfactionSurvey()
    {
        return $this->hasOne(SatisfactionSurvey::class, 'ticket_id', 'id');
    }

    /**
     * Ticket ID Format (Access: ticket_id) e.g. INC001
     */
    public function getTicketIdAttribute()
    {
        return 'INC' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }
}
