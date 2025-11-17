<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@school.com',
            'password' => Hash::make('password'), // Change this in production!
            'role' => 'admin',
            'locale' => 'en',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create a sample teacher user
        User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@school.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'locale' => 'en',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create a sample parent user
        User::create([
            'name' => 'Jane Parent',
            'email' => 'parent@school.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'locale' => 'en',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('✓ Users seeded successfully');
        $this->command->info('  Admin: admin@school.com / password');
        $this->command->info('  Teacher: teacher@school.com / password');
        $this->command->info('  Parent: parent@school.com / password');
    }
}
