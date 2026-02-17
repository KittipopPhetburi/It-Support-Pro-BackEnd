<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MaintenanceHistory Model - โมเดลประวัติการซ่อมบำรุง
 * 
 * บันทึกประวัติการซ่อมของสินทรัพย์แต่ละชิ้น
 * 
 * @property int $id
 * @property int $asset_id สินทรัพย์ที่ซ่อม
 * @property int|null $incident_id อ้างอิงถึง Ticket Incident (ถ้ามี)
 * @property string $title หัวข้อการซ่อม
 * @property string $repair_status ผลการซ่อม
 * @property boolean $has_cost มีค่าใช้จ่ายหรือไม่
 * @property decimal $cost ค่าซ่อม
 */
class MaintenanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'incident_id',
        'title',
        'description',
        'repair_status',
        'technician_id',
        'technician_name',
        'start_date',
        'completion_date',
        'has_cost',
        'cost',
        'replacement_equipment',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'completion_date' => 'datetime',
        'has_cost' => 'boolean',
        'cost' => 'decimal:2',
    ];

    /**
     * สินทรัพย์ที่ซ่อม
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Incident ที่เกี่ยวข้อง
     */
    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    /**
     * ช่างที่ดำเนินการซ่อม
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
