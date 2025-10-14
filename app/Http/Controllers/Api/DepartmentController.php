<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index()
    {
        try {
            $departments = Department::with('departmentCategory')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json($departments);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display departments by department category.
     */
    public function show($departmentCategoryId)
    {
        try {
            $departments = Department::with('departmentCategory')
                ->where('department_category_id', $departmentCategoryId)
                ->orderBy('name', 'asc')
                ->get();

            return response()->json($departments);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
