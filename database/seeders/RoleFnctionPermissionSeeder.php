<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleFnctionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('role_fnction_permission')->insert([
            ['role_code' => 'ADMIN', 'fnction_code' => 'USER', 'permission_code' => 'VIEW'],
            ['role_code' => 'ADMIN', 'fnction_code' => 'USER', 'permission_code' => 'CREATE'],
            ['role_code' => 'ADMIN', 'fnction_code' => 'ROLE', 'permission_code' => 'VIEW'],
            ['role_code' => 'MANAGER', 'fnction_code' => 'USER', 'permission_code' => 'VIEW'],
        ]);
    }
}
