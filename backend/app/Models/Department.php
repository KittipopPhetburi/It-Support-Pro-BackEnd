<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Department Model - โมเดลแผนก
 * 
 * จัดการข้อมูลแผนกภายในสาขา
 * 
 * @property int $id
 * @property string $name ชื่อแผนก
 * @property string|null $code รหัสแผนก
 * @property int|null $branch_id สาขาที่สังกัด
 * @property string|null $description คำอธิบาย
 * @property string $status สถานะ (Active/Inactive)
 * @property string|null $organization องค์กร
 */
class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'branch_id',
        'description',
        'status',
        'organization',
    ];

    /**
     * สาขาที่แผนกนี้สังกัด
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * พนักงานในแผนกนี้
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}