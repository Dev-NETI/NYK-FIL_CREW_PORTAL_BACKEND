<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentTypesController extends Controller
{
    public function index(): JsonResponse
    {
        $departmentTypes = DepartmentCategory::get();
        return response()->json($departmentTypes);
    }

    public function show($id)
    {
        return response()->json(['message' => 'show ' . $id]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'store']);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'update ' . $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'destroy ' . $id]);
    }

    public function viewDepartments($id)
    {
        $departments = Department::where('department_category_id', $id)->get();
        return response()->json($departments);
    }
}
