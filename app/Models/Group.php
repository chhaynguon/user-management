<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Group extends Model
{
    use HasFactory;

    protected $primaryKey = 'code';
    public $incrementing = false; // because it's not an ID
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

    // Group has many roles
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'group_has_roles',
            'group_code',
            'role_code',
            'code',
            'code'
        );
    }

    // Group has many users
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_has_groups',
            'group_code',
            'user_id',
            'code',
            'id'
        );
    }
}
