<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\InquiryMessage;
use Illuminate\Http\Request;

class AdminMessageContoller extends Controller
{
    public function index()
    {
        $all = Inquiry::all();
        return response()->json($all);
    }

    public function show($id)
    {
        $item = InquiryMessage::where('inquiry_id', $id)->get();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        return response()->json($item);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'inquiry_id' => 'required|integer',
            'message' => 'required|string|max:1000',
            'is_staff_reply' => 'required|boolean',
            'modified_by' => 'required|string|max:1000',
        ]);

        try {
            InquiryMessage::create($validated);
            return response()->json(['message' => 'stored'], 201);
        } catch (\Exception $th) {
            return response()->json(['message' => $th], 401);
        }
    }

    public function markRead($id)
    {
        try {
            InquiryMessage::where('inquiry_id', $id)
                ->update(['read_at' => now()]);

            return response()->json(['message' => 'Messages marked as read'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }



    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'update ' . $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'destroy ' . $id]);
    }
}
