<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\InquiryMessage;
use App\Models\User;
use Illuminate\Http\Request;

class AdminInquiryController extends Controller
{

    public function index()
    {
        $all = Inquiry::all();
        return response()->json($all);
    }

    public function show($id)
    {
        $user = User::where('id', $id)->first();
        $inquiry = Inquiry::where('department_id', $user->department_id)->with(['messages', 'crew.profile'])->orderBy('created_at', 'DESC')->get();
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
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $data = Inquiry::where('id', $id)->first();

        if (!$data) {
            return response()->json(['message' => 'Records Not Found'], 404);
        } else {
            $data->update($validated);
            return response()->json(['message' => 'update ' . $id]);
        }
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'destroy ' . $id]);
    }
}
