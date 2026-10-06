<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoUser = User::query()->create([
            'name' => 'Demo Operator',
            'email' => 'demo@example.test',
            'password' => 'password',
        ]);

        $demoUser->tickets()->create([
            'title' => 'Review webhook authorization',
            'description' => 'Synthetic ticket used by the local portfolio demo.',
        ]);

        User::query()->create([
            'name' => 'Separate Tenant',
            'email' => 'other@example.test',
            'password' => 'password',
        ]);
    }
}
