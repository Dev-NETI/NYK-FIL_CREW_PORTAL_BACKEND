<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\InquiryMessage;
use Illuminate\Http\Request;

class InquiryMessageController extends Controller
{
    public function index()
    {
        $items = InquiryMessage::orderBy('created_at', 'DESC')->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ], 200);
    }

    /**
     * Display the specified resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inquiry_id' => 'required|int|max:255',
            'user_id' => 'required|int|max:255',
            'message' => 'required|string|max:5000',
            'modified_by' => 'required|string|max:5000',
        ]);

        $item = InquiryMessage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Item created successfully',
            'data' => $item
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $item = InquiryMessage::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            // Add your validation rules here
        ]);

        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully',
            'data' => $item
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $item = InquiryMessage::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully'
        ], 200);
    }
}
