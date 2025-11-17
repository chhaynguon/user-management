<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        Role::insert([
            [
                'code' => 'ADMIN',
                'name' => 'Administrator',
                'description' => 'Full access to system',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'code' => 'MANAGER',
                'name' => 'Manager',
                'description' => 'Manager access',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
