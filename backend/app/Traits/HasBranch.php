<?php

namespace App\Traits;

use App\Scopes\BranchScope;

trait HasBranch
{
    /**
     * Boot the HasBranch trait.
     * - เพิ่ม BranchScope เพื่อกรองข้อมูลตามสาขา
     * - Auto-set branch_id จาก User ที่ login เมื่อสร้างข้อมูลใหม่
     */
    protected static function bootHasBranch()
    {
        // เพิ่ม Global Scope สำหรับกรองข้อมูลตามสาขา
        static::addGlobalScope(new BranchScope);
        
        // Auto-set branch_id เมื่อสร้างข้อมูลใหม่
        static::creating(function ($model) {
            if (empty($model->branch_id) && auth()->check()) {
                $model->branch_id = auth()->user()->branch_id;
            }
        });
    }
}
