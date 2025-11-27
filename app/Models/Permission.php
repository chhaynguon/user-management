<?php

namespace App\Models;

use App\Models\Fnction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Permission extends Model
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

    // Permission belongs to many functions
    public function fnctions()
    {
        return $this->belongsToMany(
            Fnction::class,
            'fnction_has_permissions',
            'permission_code',
            'fnction_code',
            'code',
            'code'
        );
    }

    // Permission belongs to many roles
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_has_permissions',
            'permission_code',
            'role_code',
            'code',
            'code'
        )->withPivot('fnction_code', 'fnc_perm_code')
        ->withTimestamps();
    }

    // Permission belongs to many users
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_has_permissions',
            'permission_code',
            'user_id',
            'code',
            'id'
        )->withPivot('fnction_code', 'fnc_perm_code');
    }
}
