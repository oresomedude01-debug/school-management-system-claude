<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::with('user')->latest()->paginate(15);
        return response()->json($teachers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'employee_id' => 'required|unique:teachers',
            'joining_date' => 'required|date',
            'qualification' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'teacher',
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id,
                'joining_date' => $request->joining_date,
                'qualification' => $request->qualification,
                'salary' => $request->salary,
                'specialization' => $request->specialization,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'status' => $request->status ?? 'active',
            ]);

            $teacher->load('user');
            DB::commit();

            return response()->json(['success' => true, 'data' => $teacher], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        return Teacher::with(['user', 'subjects', 'classesAsTeacher'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        DB::beginTransaction();
        try {
            $teacher->user->update($request->only(['name', 'email', 'phone', 'address']));
            $teacher->update($request->except(['name', 'email', 'password']));
            DB::commit();
            return response()->json(['success' => true, 'data' => $teacher->load('user')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        DB::beginTransaction();
        try {
            $user = $teacher->user;
            $teacher->delete();
            $user->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Teacher deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
