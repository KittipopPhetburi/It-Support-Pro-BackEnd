<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PmProject Model - โมเดลโครงการ PM
 * 
 * จัดการโครงการบำรุงรักษา (Preventive Maintenance Project)
 * 
 * @property int $id
 * @property string $project_code รหัสโครงการ
 * @property string $name ชื่อโครงการ
 * @property date $start_date วันเริ่ม
 * @property date $end_date วันสิ้นสุด
 * @property decimal $budget งบประมาณ
 * @property int|null $manager_id ผู้จัดการโครงการ
 * @property string $status สถานะโครงการ
 * @property string|null $contract_file ไฟล์สัญญา
 * @property string|null $tor_file ไฟล์ TOR
 */
class PmProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'organization',
        'department',
        'start_date',
        'end_date',
        'project_value',
        'project_manager_id',
        'description',
        'status',
        'contract_file_path',
        'tor_file_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'project_value' => 'decimal:2',
    ];

    /**
     * ผู้จัดการโครงการ
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    /**
     * สร้างรหัสโครงการถัดไป (PRJ-XXXX)
     * Note: Missing project_code column in DB, returning null/empty for now
     */
    public static function generateProjectCode(): string
    {
        return '';
    }
}
