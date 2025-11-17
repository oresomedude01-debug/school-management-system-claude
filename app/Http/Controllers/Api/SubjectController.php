<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::with(['class', 'teacher.user']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|unique:subjects',
            'class_id' => 'required|exists:classes,id',
        ]);

        $subject = Subject::create($request->all());
        return response()->json(['success' => true, 'data' => $subject], 201);
    }

    public function show($id)
    {
        return Subject::with(['class', 'teacher.user', 'grades'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $subject->update($request->all());
        return response()->json(['success' => true, 'data' => $subject]);
    }

    public function destroy($id)
    {
        Subject::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Subject deleted successfully']);
    }
}
