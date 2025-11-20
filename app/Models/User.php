<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = ['name', 'email', 'password'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    // User belongs to many groups
    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'user_has_groups',
            'user_id',     // foreign key on pivot for user
            'group_code',  // foreign key on pivot for group
            'id',          // local key on user
            'code'         // local key on group
        );
    }

    // User belongs to many roles
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'user_has_roles',
            'user_id',
            'role_code',
            'id',
            'code'
        )->with('permissions');
    }

    // User belongs to many permissions
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'user_has_permissions',
            'user_id',
            'permission_code', // pivot key
            'id',
            'code'
        )->withPivot('fnction_code', 'fnc_perm_code');
    }
}
