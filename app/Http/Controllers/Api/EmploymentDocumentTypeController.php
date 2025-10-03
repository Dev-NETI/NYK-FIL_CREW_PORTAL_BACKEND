<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploymentDocumentType;
use Illuminate\Http\JsonResponse;

class EmploymentDocumentTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $employmentDocumentTypes = EmploymentDocumentType::all();

        return response()->json($employmentDocumentTypes);
    }
}
