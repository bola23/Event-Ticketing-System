<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'CCS Admin',
            'email' => 'admin@ccs.test',
            'password' => bcrypt('password123'),
        ]);
    }
}
