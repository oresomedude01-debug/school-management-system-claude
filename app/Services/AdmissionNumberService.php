<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\DB;

class AdmissionNumberService
{
    /**
     * Generate unique admission number for a student
     *
     * Format: {YEAR}{MONTH}-{POSITION_IN_MONTH}-{OVERALL_POSITION}
     * Example: 202401-01-00123
     *
     * @param Student $student
     * @return string
     */
    public function generate(Student $student): string
    {
        return DB::transaction(function () use ($student) {
            // Extract year and month from admission date
            $admissionDate = $student->admission_date;
            $year = $admissionDate->year;
            $month = $admissionDate->month;

            // Update student with extracted year and month
            $student->admission_year = $year;
            $student->admission_month = $month;

            // Find position in month
            $positionInMonth = Student::where('admission_year', $year)
                ->where('admission_month', $month)
                ->where('id', '!=', $student->id)
                ->count() + 1;

            // Find overall position (total count across all time)
            $overallPosition = Student::where('id', '!=', $student->id)->count() + 1;

            // Update student with positions
            $student->admission_position_in_month = $positionInMonth;
            $student->admission_position_overall = $overallPosition;

            // Build admission number
            $yearStr = (string) $year;
            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
            $posInMonthStr = str_pad($positionInMonth, 2, '0', STR_PAD_LEFT);
            $overallStr = str_pad($overallPosition, 5, '0', STR_PAD_LEFT);

            $admissionNumber = "{$yearStr}{$monthStr}-{$posInMonthStr}-{$overallStr}";

            // Ensure uniqueness (in case of race condition)
            $counter = 1;
            $originalNumber = $admissionNumber;
            while (Student::where('admission_number', $admissionNumber)->where('id', '!=', $student->id)->exists()) {
                $admissionNumber = $originalNumber . '-' . $counter;
                $counter++;
            }

            // Save admission number to student
            $student->admission_number = $admissionNumber;
            $student->save();

            return $admissionNumber;
        });
    }

    /**
     * Regenerate admission number for a student (if needed)
     *
     * @param Student $student
     * @return string
     */
    public function regenerate(Student $student): string
    {
        return $this->generate($student);
    }

    /**
     * Parse admission number to extract components
     *
     * @param string $admissionNumber
     * @return array
     */
    public function parse(string $admissionNumber): array
    {
        // Format: YYYYMM-XX-YYYYY
        $parts = explode('-', $admissionNumber);

        if (count($parts) < 3) {
            return [
                'year' => null,
                'month' => null,
                'position_in_month' => null,
                'overall_position' => null,
            ];
        }

        $yearMonth = $parts[0];
        $year = substr($yearMonth, 0, 4);
        $month = substr($yearMonth, 4, 2);

        return [
            'year' => (int) $year,
            'month' => (int) $month,
            'position_in_month' => (int) $parts[1],
            'overall_position' => (int) $parts[2],
        ];
    }
}
