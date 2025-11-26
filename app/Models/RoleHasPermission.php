<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleHasPermission extends Model
{
    use HasFactory;

    protected $table = 'role_has_permissions';
    protected $primaryKey = 'fnc_perm_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'role_code',
        'fnction_code',
        'permission_code',
        'fnc_perm_code'
    ];

    // Role relationship
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_code', 'code');
    }

    // Function relationship
    public function fnction()
    {
        return $this->belongsTo(Fnction::class, 'fnction_code', 'code');
    }

    // Permission relationship
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_code', 'code');
    }
}
