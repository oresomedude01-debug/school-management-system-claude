<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'class']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('admission_number', 'like', "%{$search}%");
        }

        $students = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'admission_number' => 'required|unique:students',
            'admission_date' => 'required|date',
            'class_id' => 'nullable|exists:classes,id',
            'roll_number' => 'nullable|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string',
            'parent_name' => 'required|string',
            'parent_phone' => 'required|string',
            'parent_email' => 'nullable|email',
            'medical_info' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,graduated',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'admission_number' => $request->admission_number,
                'admission_date' => $request->admission_date,
                'class_id' => $request->class_id,
                'roll_number' => $request->roll_number,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'parent_name' => $request->parent_name,
                'parent_phone' => $request->parent_phone,
                'parent_email' => $request->parent_email,
                'medical_info' => $request->medical_info,
                'status' => $request->status ?? 'active',
            ]);

            $student->load(['user', 'class']);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'data' => $student
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $student = Student::with(['user', 'class', 'grades.subject', 'attendance'])->findOrFail($id);
        return response()->json($student);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $student->user_id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'admission_number' => 'sometimes|unique:students,admission_number,' . $id,
            'admission_date' => 'sometimes|date',
            'class_id' => 'nullable|exists:classes,id',
            'roll_number' => 'nullable|string',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'blood_group' => 'nullable|string',
            'parent_name' => 'sometimes|string',
            'parent_phone' => 'sometimes|string',
            'parent_email' => 'nullable|email',
            'medical_info' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,graduated',
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('name') || $request->has('email') || $request->has('phone') || $request->has('address')) {
                $student->user->update($request->only(['name', 'email', 'phone', 'address']));
            }

            $student->update($request->except(['name', 'email', 'password']));
            $student->load(['user', 'class']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'data' => $student
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        DB::beginTransaction();
        try {
            $user = $student->user;
            $student->delete();
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
