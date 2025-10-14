<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepartmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepartmentCategoryController extends Controller
{
    /**
     * Display a listing of department categories.
     */
    public function index()
    {
        try {
            $categories = DepartmentCategory::orderBy('name', 'asc')->get();

            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
