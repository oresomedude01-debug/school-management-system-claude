<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\Section;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            ['name' => 'Grade 1', 'name_ar' => 'الصف الأول', 'level' => 1],
            ['name' => 'Grade 2', 'name_ar' => 'الصف الثاني', 'level' => 2],
            ['name' => 'Grade 3', 'name_ar' => 'الصف الثالث', 'level' => 3],
            ['name' => 'Grade 4', 'name_ar' => 'الصف الرابع', 'level' => 4],
            ['name' => 'Grade 5', 'name_ar' => 'الصف الخامس', 'level' => 5],
            ['name' => 'Grade 6', 'name_ar' => 'الصف السادس', 'level' => 6],
            ['name' => 'Grade 7', 'name_ar' => 'الصف السابع', 'level' => 7],
            ['name' => 'Grade 8', 'name_ar' => 'الصف الثامن', 'level' => 8],
            ['name' => 'Grade 9', 'name_ar' => 'الصف التاسع', 'level' => 9],
            ['name' => 'Grade 10', 'name_ar' => 'الصف العاشر', 'level' => 10],
            ['name' => 'Grade 11', 'name_ar' => 'الصف الحادي عشر', 'level' => 11],
            ['name' => 'Grade 12', 'name_ar' => 'الصف الثاني عشر', 'level' => 12],
        ];

        foreach ($classes as $classData) {
            $class = ClassModel::create([
                'name' => $classData['name'],
                'name_ar' => $classData['name_ar'],
                'numeric_level' => $classData['level'],
                'capacity' => 120, // 3 sections × 40 students
                'is_active' => true,
            ]);

            // Create 3 sections (A, B, C) for each class
            $sections = ['A', 'B', 'C'];
            foreach ($sections as $sectionName) {
                Section::create([
                    'class_id' => $class->id,
                    'name' => $sectionName,
                    'capacity' => 40,
                    'room_number' => $classData['level'] . $sectionName,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✓ Classes and sections seeded successfully');
        $this->command->info('  12 classes (Grade 1-12) with 3 sections each (A, B, C)');
    }
}
