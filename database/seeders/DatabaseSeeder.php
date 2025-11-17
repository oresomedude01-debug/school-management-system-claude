<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding School Management System Database...');
        $this->command->newLine();

        $this->call([
            UserSeeder::class,
            ClassSeeder::class,
            SubjectSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('You can now login with:');
        $this->command->info('  URL: /login');
        $this->command->info('  Email: admin@school.com');
        $this->command->info('  Password: password');
    }
}
