<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    /**
     * Display a listing of programs.
     */
    public function index()
    {
        $programs = Program::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $programs,
            'message' => 'Programs retrieved successfully'
        ]);
    }

    /**
     * Store a newly created program.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:programs',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $program = Program::create($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $program,
            'message' => 'Program created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified program.
     */
    public function show(Program $program)
    {
        return response()->json([
            'success' => true,
            'data' => $program,
            'message' => 'Program retrieved successfully'
        ]);
    }

    /**
     * Update the specified program.
     */
    public function update(Request $request, Program $program)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:programs,name,' . $program->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $program->update($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $program,
            'message' => 'Program updated successfully'
        ]);
    }

    /**
     * Remove the specified program.
     */
    public function destroy(Program $program)
    {
        $program->delete();

        return response()->json([
            'success' => true,
            'message' => 'Program deleted successfully'
        ]);
    }
}