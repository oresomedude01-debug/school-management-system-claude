<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        return Classes::with(['classTeacher.user', 'students'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'section' => 'nullable|string',
            'capacity' => 'required|integer',
        ]);

        $class = Classes::create($request->all());
        return response()->json(['success' => true, 'data' => $class], 201);
    }

    public function show($id)
    {
        return Classes::with(['classTeacher.user', 'students.user', 'subjects'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $class = Classes::findOrFail($id);
        $class->update($request->all());
        return response()->json(['success' => true, 'data' => $class]);
    }

    public function destroy($id)
    {
        Classes::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Class deleted successfully']);
    }
}
