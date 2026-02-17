<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * KbArticle Model - โมเดลบทความ Knowledge Base
 * 
 * จัดการบทความช่วยเหลือและคู่มือการใช้งาน
 * 
 * @property int $id
 * @property string $title หัวข้อ
 * @property string $content เนื้อหา (HTML/Markdown)
 * @property string $category หมวดหมู่
 * @property array $tags แท็ก (JSON)
 * @property string $author ชื่อผู้เขียน
 * @property int $views จำนวนการเข้าชม
 * @property int $helpful จำนวนคนกดว่ามีประโยชน์
 * @property int $not_helpful จำนวนคนกดว่าไม่มีประโยชน์
 */
class KbArticle extends Model
{
    use HasFactory;

    protected $table = 'kb_articles';

    protected $fillable = [
        'title',
        'content',
        'category',
        'tags',
        'author',
        'created_by',
        'views',
        'helpful',
        'not_helpful',
    ];

    protected $casts = [
        'tags' => 'array',
        'views' => 'integer',
        'helpful' => 'integer',
        'not_helpful' => 'integer',
    ];
}
