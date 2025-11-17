<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathematics', 'name_ar' => 'الرياضيات', 'code' => 'MATH'],
            ['name' => 'Science', 'name_ar' => 'العلوم', 'code' => 'SCI'],
            ['name' => 'English', 'name_ar' => 'اللغة الإنجليزية', 'code' => 'ENG'],
            ['name' => 'Arabic', 'name_ar' => 'اللغة العربية', 'code' => 'ARA'],
            ['name' => 'Social Studies', 'name_ar' => 'الدراسات الاجتماعية', 'code' => 'SOC'],
            ['name' => 'Islamic Studies', 'name_ar' => 'التربية الإسلامية', 'code' => 'ISL'],
            ['name' => 'Physical Education', 'name_ar' => 'التربية البدنية', 'code' => 'PE'],
            ['name' => 'Art', 'name_ar' => 'الفنون', 'code' => 'ART'],
            ['name' => 'Music', 'name_ar' => 'الموسيقى', 'code' => 'MUS'],
            ['name' => 'Computer Science', 'name_ar' => 'علوم الحاسوب', 'code' => 'CS'],
            ['name' => 'Physics', 'name_ar' => 'الفيزياء', 'code' => 'PHY'],
            ['name' => 'Chemistry', 'name_ar' => 'الكيمياء', 'code' => 'CHEM'],
            ['name' => 'Biology', 'name_ar' => 'الأحياء', 'code' => 'BIO'],
            ['name' => 'History', 'name_ar' => 'التاريخ', 'code' => 'HIST'],
            ['name' => 'Geography', 'name_ar' => 'الجغرافيا', 'code' => 'GEO'],
        ];

        foreach ($subjects as $subjectData) {
            Subject::create([
                'name' => $subjectData['name'],
                'name_ar' => $subjectData['name_ar'],
                'code' => $subjectData['code'],
                'is_active' => true,
            ]);
        }

        $this->command->info('✓ Subjects seeded successfully');
        $this->command->info('  15 subjects created');
    }
}
