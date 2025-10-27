<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AdminMessageNotification;
use App\Models\Department;
use App\Models\Inquiry;
use App\Models\InquiryMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
            // Create the message
            InquiryMessage::create($validated);

            // Fetch inquiry details with relationships
            $inquiry = Inquiry::with(['crew.profile'])->findOrFail($validated['inquiry_id']);

            // Fetch the user who sent the message
            $user = User::with('profile', 'adminProfile')->findOrFail($validated['user_id']);

            // Determine sender name and email
            $senderName = 'System-Generated';
            $senderEmail = $user->email ?? 'no-reply@nykfil.com';

            if ($user->is_crew && $user->profile) {
                $senderName = $user->profile->full_name ?? $user->name ?? 'Crew Member';
            } elseif (!$user->is_crew && $user->adminProfile) {
                $senderName = $user->adminProfile->full_name ?? $user->name ?? 'Admin User';
            }

            // Send email notification to noc@neti.com.ph
            Mail::to('noc@neti.com.ph')->queue(
                new AdminMessageNotification(
                    messageContent: $validated['message'],
                    senderName: $senderName,
                    senderEmail: $senderEmail,
                    inquirySubject: $inquiry->subject ?? 'No Subject',
                    inquiryId: $validated['inquiry_id']
                )
            );

            return response()->json(['message' => 'stored'], 201);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store message',
                'error' => $th->getMessage()
            ], 500);
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
