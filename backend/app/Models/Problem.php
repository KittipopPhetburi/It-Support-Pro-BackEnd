<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Problem Model - โมเดลปัญหา (Root Cause)
 * 
 * จัดการปัญหาที่เกิดจากหลาย Incident ที่มีสาเหตุเดียวกัน (Problem Management)
 * เน้นการหาสาเหตุรากฐาน (Root Cause) และวิธีแก้ปัญหาถาวร
 * 
 * @property int $id
 * @property string $title หัวข้อปัญหา
 * @property string $root_cause สาเหตุรากฐาน
 * @property string $workaround วิธีแก้ปัญหาชั่วคราว
 * @property string $solution วิธีแก้ปัญหาถาวร
 * @property string $status สถานะ
 */
class Problem extends Model
{
    use HasFactory, HasBranch;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'assigned_to_id',
        'root_cause',
        'workaround',
        'solution',
        'branch_id',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * ผู้รับผิดชอบแก้ไขปัญหา
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * Incidents ที่เกี่ยวข้องกับปัญหานี้
     */
    public function incidents()
    {
        return $this->belongsToMany(Incident::class, 'incident_problem')
                    ->withTimestamps();
    }
}
