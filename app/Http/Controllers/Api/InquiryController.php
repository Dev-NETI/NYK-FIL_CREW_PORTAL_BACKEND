<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\InquiryMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index(): JsonResponse
    {
        $inquiry = Inquiry::with('messages')->get();
        return response()->json($inquiry);
    }

    public function show($id)
    {
        $inquiry = Inquiry::where('crew_id', $id)->with(['messages', 'department'])->get();
        return response()->json($inquiry);
    }

    public function store(Request $request)
    {
        $user = User::with('profile')->where('id', $request->crew_id)->first();
        $fullName = $user->profile->first_name . ' ' . $user->profile->last_name;

        $validatedInquiry = $request->validate([
            'crew_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'subject' => 'required|string|max:255',
        ]);

        $validatedInquiry['modified_by'] = $fullName;

        $inquiry = Inquiry::create($validatedInquiry);

        $inquiryId = $inquiry->id;

        $validatedInquiryMessages = $request->validate([
            'crew_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $inquiryMessage = InquiryMessage::create([
            'inquiry_id' => $inquiryId,
            'user_id' => $validatedInquiryMessages['crew_id'],
            'message' => $validatedInquiryMessages['message'],
            'modified_by' => $fullName
        ]);

        return response()->json([
            'message' => 'Inquiry and message created successfully',
            'inquiry' => $inquiry,
            'first_message' => $inquiryMessage
        ], 201);
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
