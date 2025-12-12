<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * กรองข้อมูลตาม branch_id ของ User ที่ login อยู่
     * ยกเว้น Admin จะเห็นข้อมูลทั้งหมด
     */
    public function apply(Builder $builder, Model $model)
    {
        // ลองหลายวิธีในการดึง user - รองรับทั้ง web และ API (Sanctum)
        $user = request()->user() ?? auth('sanctum')->user() ?? auth()->user();
        
        // Debug log
        \Log::info('BranchScope: Table=' . $model->getTable() . ', User=' . ($user ? $user->name . ' (role=' . $user->role . ', branch_id=' . $user->branch_id . ')' : 'NULL'));
        
        // ถ้ามี User login และไม่ใช่ admin ให้กรองตาม branch_id
        if ($user && strtolower($user->role) !== 'admin') {
            $builder->where($model->getTable() . '.branch_id', $user->branch_id);
            \Log::info('BranchScope: Filtering by branch_id=' . $user->branch_id);
        }
    }
}

