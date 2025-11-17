<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classes;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'total_teachers' => Teacher::count(),
            'active_teachers' => Teacher::where('status', 'active')->count(),
            'total_classes' => Classes::count(),
            'attendance_today' => Attendance::whereDate('date', today())->count(),
            'present_today' => Attendance::whereDate('date', today())->where('status', 'present')->count(),
        ];

        return response()->json($stats);
    }

    public function recentActivity()
    {
        $recentStudents = Student::with('user')->latest()->take(5)->get();
        $recentAttendance = Attendance::with('student.user')->latest()->take(10)->get();

        return response()->json([
            'recent_students' => $recentStudents,
            'recent_attendance' => $recentAttendance,
        ]);
    }
}
