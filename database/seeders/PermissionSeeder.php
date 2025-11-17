<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        Permission::insert([
            [
                'code' => 'VIEW',
                'name' => 'View',
                'description' => 'View access',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'code' => 'CREATE',
                'name' => 'Create',
                'description' => 'Create access',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'code' => 'UPDATE',
                'name' => 'Update',
                'description' => 'Update access',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'code' => 'DELETE',
                'name' => 'Delete',
                'description' => 'Delete access',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
