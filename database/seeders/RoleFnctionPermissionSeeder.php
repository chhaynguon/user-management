<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleFnctionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('role_function_permission')->insert([
            ['role_code' => 'ADMIN', 'function_code' => 'USER', 'permission_code' => 'VIEW'],
            ['role_code' => 'ADMIN', 'function_code' => 'USER', 'permission_code' => 'CREATE'],
            ['role_code' => 'ADMIN', 'function_code' => 'ROLE', 'permission_code' => 'VIEW'],
            ['role_code' => 'MANAGER', 'function_code' => 'USER', 'permission_code' => 'VIEW'],
        ]);
    }
}
