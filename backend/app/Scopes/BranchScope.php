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
        $user = auth()->user();
        
        // ถ้ามี User login และไม่ใช่ admin ให้กรองตาม branch_id
        if ($user && strtolower($user->role) !== 'admin') {
            $builder->where($model->getTable() . '.branch_id', $user->branch_id);
        }
    }
}
