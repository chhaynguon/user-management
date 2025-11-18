<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    /**
     * Permissions assigned to this role
     * Pivot table: role_fnction_permission
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_fnction_permission',
            'role_code',
            'permission_code',
            'code',
            'code'
        )->withPivot('fnction_code');
    }

    /**
     * Functions assigned to this role
     * Pivot table: role_fnction_permission
     *
     * Because fnction_code is also part of the same pivot,
     * we can get all functions declared for this role.
     */
    public function fnctions()
    {
        return $this->belongsToMany(
            Fnction::class,
            'role_fnction_permission',
            'role_code',
            'fnction_code',
            'code'
        )->distinct();
    }

    /**
     * Groups that include this role
     * Pivot table: group_role
     */
    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'group_role',
            'role_code',
            'group_code',
            'code',
            'code'
        );
    }

}
