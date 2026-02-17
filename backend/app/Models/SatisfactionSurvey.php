<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SatisfactionSurvey Model - โมเดลแบบสำรวจความพึงพอใจ
 * 
 * ใช้สำหรับเก็บผลประเมินความพึงพอใจหลังจากปิดงาน (Ticket Closed)
 * 
 * @property int $id
 * @property int $ticket_id Ticket ที่เกี่ยวข้อง (Incident ID)
 * @property int $rating คะแนนความพึงพอใจ (1-5)
 * @property string|null $feedback ข้อเสนอแนะ
 * @property int|null $respondent_id ผู้ประเมิน
 * @property datetime $submitted_at เวลาที่ประเมิน
 */
class SatisfactionSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'rating',
        'feedback',
        'respondent_id',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * ผู้ประเมิน
     */
    public function respondent()
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }

    /**
     * Incident ที่ถูกประเมิน
     */
    public function incident()
    {
        return $this->belongsTo(Incident::class, 'ticket_id', 'id');
    }
}
