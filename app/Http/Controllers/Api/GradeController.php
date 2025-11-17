<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Grade::with(['student.user', 'subject']);

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        return $query->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_type' => 'required|string',
            'marks_obtained' => 'required|numeric',
            'total_marks' => 'required|numeric',
            'exam_date' => 'required|date',
        ]);

        $grade = Grade::create($request->all());
        return response()->json(['success' => true, 'data' => $grade], 201);
    }

    public function show($id)
    {
        return Grade::with(['student.user', 'subject'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);
        $grade->update($request->all());
        return response()->json(['success' => true, 'data' => $grade]);
    }

    public function destroy($id)
    {
        Grade::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Grade deleted successfully']);
    }
}
