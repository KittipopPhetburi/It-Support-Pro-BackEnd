<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SubContractor Model - โมเดลผู้รับเหมาช่วง
 * 
 * จัดการข้อมูล Supplier / Vendor / Subcontractor
 * 
 * @property int $id
 * @property string $name ชื่อผู้ติดต่อ
 * @property string $company ชื่อบริษัท
 * @property string|null $email อีเมล
 * @property string|null $phone เบอร์โทรศัพท์
 * @property string|null $specialty ความเชี่ยวชาญ
 * @property string|null $province จังหวัด
 * @property string $status สถานะ
 */
class SubContractor extends Model
{
    use HasFactory;

    protected $table = 'sub_contractors';

    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'specialty',
        'province',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'status',
    ];
}
