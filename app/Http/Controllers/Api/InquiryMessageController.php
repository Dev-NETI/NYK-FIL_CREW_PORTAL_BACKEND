<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AdminMessageNotification;
use App\Models\Inquiry;
use App\Models\InquiryMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

    public function markRead($id)
    {
        try {
            InquiryMessage::where('inquiry_id', $id)->where('is_staff_reply', 1)
                ->update(['read_at' => now()]);
            return response()->json(['message' => 'Messages marked as read'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
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

        try {
            // Create the message
            $item = InquiryMessage::create($validated);

            // Fetch inquiry details with relationships
            $inquiry = Inquiry::with(['crew.profile'])->findOrFail($validated['inquiry_id']);
            // Fetch the user who sent the message
            $user = User::with('profile', 'adminProfile')->findOrFail($validated['user_id']);

            // Determine sender name and email
            $senderName = 'Unknown User';
            $senderEmail = $user->email ?? 'no-reply@nykfil.com';

            if ($user->is_crew && $user->profile) {
                $senderName = $user->profile->full_name ?? $user->name ?? 'Crew Member';
            } elseif (!$user->is_crew && $user->adminProfile) {
                $senderName = $user->adminProfile->full_name ?? $user->name ?? 'Admin User';
            }

            // Get department users (where is_crew is NULL and department_id matches)
            $departmentUsers = User::where('is_crew', 0)
                ->where('department_id', $inquiry->department_id)
                ->whereNotNull('email')
                ->pluck('email')
                ->toArray();

            // Combine noc email with department user emails
            $recipients = $departmentUsers;

            // Send email notification to all recipients
            Mail::to($recipients)->send(
                new AdminMessageNotification(
                    messageContent: $validated['message'],
                    senderName: $senderName,
                    senderEmail: $senderEmail,
                    inquirySubject: $inquiry->subject ?? 'No Subject',
                    inquiryId: $validated['inquiry_id']
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully',
                'data' => $item
            ], 201);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item',
                'error' => $th->getMessage()
            ], 500);
        }
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
