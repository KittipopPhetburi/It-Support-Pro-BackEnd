<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ServiceCatalogItem Model - โมเดลรายการบริการ
 * 
 * จัดการ Catalog ของบริการที่มีให้ในระบบ (เช่น ขอติดตั้งโปรแกรม, ขอสิทธิ์เข้าใช้งาน)
 * 
 * @property int $id
 * @property string $name ชื่อบริการ
 * @property string $description รายละเอียด
 * @property string $category หมวดหมู่
 * @property string|null $sla SLA (ข้อตกลงระดับการบริการ)
 * @property decimal|null $cost ค่าใช้จ่าย (ถ้ามี)
 * @property string|null $icon ไอคอน
 * @property string|null $estimated_time เวลาดำเนินการโดยประมาณ
 */
class ServiceCatalogItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'sla',
        'cost',
        'icon',
        'estimated_time',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
    ];

    /**
     * คำขอบริการที่เกี่ยวข้องกับ Catalog Item นี้
     */
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'service_id');
    }
}
