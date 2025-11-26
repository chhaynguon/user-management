<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['code', 'name', 'description'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    // Role belongs to many groups
    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'group_has_roles',
            'role_code',
            'group_code',
            'code',
            'code'
        );
    }

    public function functionPermissions()
    {
        return $this->hasMany(RoleHasPermission::class, 'role_code', 'code');
    }

    // Role belongs to many users
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_has_roles',
            'role_code',
            'user_id',
            'code',
            'id'
        );
    }

    // Role has many permissions through function
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_permissions',
            'role_code',
            'permission_code',
            'code',
            'code'
        )->withPivot('fnction_code', 'fnc_perm_code');
    }
}
