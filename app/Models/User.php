<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    /**
     * USER → GROUPS
     */
    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'user_group',
            'user_id',
            'group_code',
            'id',
            'code'
        );
    }

    /**
     * USER → PERMISSIONS (via roles)
     */
    public function permissions()
    {
        return Permission::whereIn(
            'code',
            $this->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions.*.code')
                ->flatten()
        );
    }

    public function hasRole($roleCode)
    {
        return $this->roles()->pluck('code')->contains($roleCode);
    }

    public function hasPermission($permissionCode)
    {
        return $this->permissions()->pluck('code')->contains($permissionCode);
    }

    public function roles()
    {
        return Role::whereIn(
            'code',
            $this->groups()->with('roles')->get()->pluck('roles.*.code')->flatten()
        );
    }
}
