<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserGroupSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $user  = User::where('email', 'user@example.com')->first();

        if ($admin) {
            $admin->groups()->sync(['HQ']);
        }

        if ($user) {
            $user->groups()->sync(['SALES']);
        }
    }
}
