<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['code','name','description'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function permission()
    {
        return $this->roles()
        ->flatMap(fn($role) => $role->permissions)
        ->unique('code')
        ->values();
    }

}
