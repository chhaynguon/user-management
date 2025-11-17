<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fnction; // use your actual model
use Carbon\Carbon;

class FnctionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        Fnction::insert([
            [
                'code' => 'USER',
                'name' => 'User Management',
                'description' => 'Manage users',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'code' => 'ROLE',
                'name' => 'Role Management',
                'description' => 'Manage roles',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
