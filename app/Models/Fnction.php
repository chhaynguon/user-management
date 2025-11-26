<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Permission;
use Carbon\Carbon;

class Fnction extends Model
{
    use HasFactory;

    protected $table = 'fnctions';
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

    // Function has many permissions
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'fnction_has_permissions',
            'fnction_code',
            'permission_code',
            'code',
            'code'
        )->withPivot('fnc_perm_code');
    }

    // Function belongs to many roles through permissions
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_has_permissions',
            'fnction_code',
            'role_code',
            'code',
            'code'
        )->withPivot('permission_code', 'fnc_perm_code');
    }

    // Function belongs to many users through permissions
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_has_permissions',
            'fnction_code',
            'user_id',
            'code',
            'id'
        )->withPivot('permission_code', 'fnc_perm_code');
    }
}
