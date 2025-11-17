<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description'];

    // FORMAT timestamps
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    /**
     * Users belonging to this group
     *
     * Pivot table: user_group
     * group_code → groups.code
     * user_id    → users.id
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_group',
            'group_code',
            'user_id',
            'code',
            'id'
        );
    }

    /**
     * Roles belonging to this group
     *
     * Pivot table: group_role
     * group_code → groups.code
     * role_code  → roles.code
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'group_role',
            'group_code',
            'role_code',
            'code',
            'code'
        );
    }

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
}
