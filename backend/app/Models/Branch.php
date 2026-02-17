<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Branch Model - โมเดลสาขา
 * 
 * ใช้สำหรับจัดการข้อมูลสาขาภายในองค์กร
 * เชื่อมโยงกับ Departments, Users, Assets, Incidents และ Requests ต่างๆ
 * 
 * @property int $id
 * @property string $code รหัสสาขา
 * @property string $name ชื่อสาขา
 * @property string|null $address ที่อยู่
 * @property string|null $province จังหวัด
 * @property string|null $phone เบอร์โทรศัพท์
 * @property string|null $organization องค์กรต้นสังกัด
 * @property string $status สถานะ (Active/Inactive)
 * @property string|null $telegram_chat_id แชท ID สำหรับแจ้งเตือนผ่าน Telegram
 * @property array|null $notification_config การตั้งค่าการแจ้งเตือน
 */
class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'province',
        'phone',
        'organization',
        'status',
        'telegram_chat_id',
        'notification_config',
    ];

    protected $casts = [
        'notification_config' => 'array',
    ];

    /**
     * ความสัมพันธ์กับแผนกต่างๆ ในสาขานี้
     */
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    /**
     * ความสัมพันธ์กับผู้ใช้งานในสาขานี้
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * ความสัมพันธ์กับสินทรัพย์ในสาขานี้
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * ความสัมพันธ์กับ Incidents ที่เกิดขึ้นในสาขานี้
     */
    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * ความสัมพันธ์กับคำขอเบิก/ยืมสินทรัพย์ในสาขานี้
     */
    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class);
    }

    /**
     * ความสัมพันธ์กับคำขออื่นๆ ในสาขานี้
     */
    public function otherRequests()
    {
        return $this->hasMany(OtherRequest::class);
    }

    /**
     * ความสัมพันธ์กับคำขอบริการในสาขานี้
     */
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
