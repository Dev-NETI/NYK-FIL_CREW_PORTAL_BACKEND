<?php

namespace App\Traits;

use App\Mail\DocumentSubmittedToAdminMail;
use App\Mail\DocumentStatusUpdatedMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait SendsDocumentNotifications
{
    /**
     * Send email notification to admin when crew submits/updates document
     */
    protected function sendAdminNotification($update, string $action, string $category, string $documentTypeName, array $additionalDetails = []): void
    {
        try {
            $profile = $update->userProfile;
            $crewName = $profile ? trim("{$profile->first_name} {$profile->middle_name} {$profile->last_name}") : 'Unknown Crew';
            $crewId = $update->crew_id ?? 'N/A';

            // Merge basic details with additional details
            $documentDetails = array_merge([
                'Submitted At' => $update->created_at?->format('F d, Y - h:i A') ?? 'N/A',
            ], $additionalDetails);

            Mail::to('noc@neti.com.ph')->send(
                new DocumentSubmittedToAdminMail(
                    $crewName,
                    $crewId,
                    $documentTypeName,
                    $category,
                    $action,
                    $documentDetails
                )
            );

            Log::info("Admin notification email sent", [
                'crew_id' => $crewId,
                'document_category' => $category,
                'document_type' => $documentTypeName,
                'action' => $action,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send admin notification email", [
                'error' => $e->getMessage(),
                'crew_id' => $update->crew_id ?? 'N/A',
                'category' => $category,
            ]);
        }
    }

    /**
     * Send email notification to crew when document is approved/rejected
     */
    protected function sendCrewNotification(
        $update,
        string $status,
        string $category,
        string $documentTypeName,
        string $reviewerName,
        array $documentDetails = [],
        ?string $rejectionReason = null
    ): void {
        try {
            $profile = $update->userProfile;
            $crewName = $profile ? trim("{$profile->first_name} {$profile->middle_name} {$profile->last_name}") : 'Crew Member';

            // Get crew's email from users table
            $user = User::where('crew_id', $update->crew_id)->first();
            if (!$user || !$user->email) {
                Log::warning("Cannot send email notification: No email found for crew", [
                    'crew_id' => $update->crew_id,
                ]);
                return;
            }

            Mail::to($user->email)->send(
                new DocumentStatusUpdatedMail(
                    $crewName,
                    $documentTypeName,
                    $category,
                    $status,
                    $reviewerName,
                    $documentDetails,
                    $rejectionReason
                )
            );

            Log::info("Crew notification email sent", [
                'crew_id' => $update->crew_id,
                'crew_email' => $user->email,
                'document_category' => $category,
                'document_type' => $documentTypeName,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send crew notification email", [
                'error' => $e->getMessage(),
                'crew_id' => $update->crew_id ?? 'N/A',
                'status' => $status,
                'category' => $category,
            ]);
        }
    }
}
