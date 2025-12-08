<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'name', 'group', 'sort_order'];

    public function permissions(): HasMany
    {
        return $this->hasMany(RoleMenuPermission::class);
    }
}
