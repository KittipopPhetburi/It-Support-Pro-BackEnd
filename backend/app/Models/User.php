<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'branch_id',
        'department_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function incidentsRequested()
    {
        return $this->hasMany(Incident::class, 'requester_id');
    }

    public function incidentsAssigned()
    {
        return $this->hasMany(Incident::class, 'assignee_id');
    }

    public function problemsOwned()
    {
        return $this->hasMany(Problem::class, 'owner_id');
    }

    public function kbArticlesCreated()
    {
        return $this->hasMany(KbArticle::class, 'created_by_id');
    }

    public function kbArticlesUpdated()
    {
        return $this->hasMany(KbArticle::class, 'updated_by_id');
    }

    public function assetAssignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function assetRequestsRequested()
    {
        return $this->hasMany(AssetRequest::class, 'requester_id');
    }

    public function assetRequestsApproved()
    {
        return $this->hasMany(AssetRequest::class, 'approver_id');
    }

    public function otherRequestsRequested()
    {
        return $this->hasMany(OtherRequest::class, 'requester_id');
    }

    public function otherRequestsHandled()
    {
        return $this->hasMany(OtherRequest::class, 'handler_id');
    }

    public function systemLogs()
    {
        return $this->hasMany(SystemLog::class);
    }
}
