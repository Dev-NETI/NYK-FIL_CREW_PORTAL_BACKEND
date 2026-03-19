<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Contract;
use App\Models\CrewCertificate;
use App\Models\CrewCertificateUpdate;
use App\Models\DebriefingForm;
use App\Models\EmploymentDocumentUpdate;
use App\Models\JobDescriptionRequest;
use App\Models\ProfileUpdateRequest;
use App\Models\TravelDocument;
use App\Models\TravelDocumentUpdate;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ─── 1. CREW STATS ────────────────────────────────────────────────────
        $totalCrew = User::crew()->count();

        // crew_status / hire_status live in user_employment, not users
        $onBoard = User::crew()
            ->whereHas('employment', fn ($q) => $q->where('crew_status', 'on_board'))
            ->count();

        $onVacation = User::crew()
            ->whereHas('employment', fn ($q) => $q->where('crew_status', 'on_vacation'))
            ->count();

        $standby = User::crew()
            ->whereHas('employment', fn ($q) => $q->where('crew_status', 'standby'))
            ->count();

        $newHires = User::crew()
            ->whereHas('employment', fn ($q) => $q->where('hire_status', 'new_hire'))
            ->count();

        $reHires = User::crew()
            ->whereHas('employment', fn ($q) => $q->where('hire_status', 're_hire'))
            ->count();

        $crewWithActiveContracts = User::crew()
            ->whereHas('contracts', fn ($q) => $q
                ->where('contract_start_date', '<=', now())
                ->where('contract_end_date', '>=', now())
            )->count();

        // Gender distribution via join with user_profiles
        $genderDistribution = DB::table('user_profiles')
            ->join('users', 'user_profiles.user_id', '=', 'users.id')
            ->where('users.is_crew', true)
            ->whereNull('users.deleted_at')
            ->whereNull('user_profiles.deleted_at')
            ->selectRaw("COALESCE(user_profiles.gender, 'unspecified') as gender, count(*) as count")
            ->groupBy('gender')
            ->get();

        // New crew registrations by month (last 6 months) — DB-agnostic
        $registrationsByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $registrationsByMonth[] = [
                'month' => $month->format('Y-m'),
                'label' => $month->format('M Y'),
                'count' => User::crew()
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }

        // Rank distribution (from user_employment joined with ranks)
        $rankDistribution = DB::table('user_employment')
            ->join('ranks', 'user_employment.rank_id', '=', 'ranks.id')
            ->join('users', 'user_employment.user_id', '=', 'users.id')
            ->where('users.is_crew', true)
            ->whereNull('users.deleted_at')
            ->whereNull('user_employment.deleted_at')
            ->whereNotNull('user_employment.rank_id')
            ->selectRaw('ranks.name as rank, count(*) as count')
            ->groupBy('ranks.name')
            ->orderByDesc('count')
            ->take(8)
            ->get();

        // ─── 2. CONTRACT STATS ────────────────────────────────────────────────
        $totalContracts   = Contract::count();
        $activeContracts  = Contract::active()->count();
        $expiredContracts = Contract::expired()->count();
        $expiring30 = Contract::expiringSoon(30)->count();
        $expiring60 = Contract::expiringSoon(60)->count();
        $expiring90 = Contract::expiringSoon(90)->count();

        // Top 5 vessels by active crew
        $topVessels = Vessel::withCount([
            'contracts as active_crew' => fn ($q) => $q
                ->where('contract_start_date', '<=', now())
                ->where('contract_end_date', '>=', now()),
        ])
            ->having('active_crew', '>', 0)
            ->orderByDesc('active_crew')
            ->take(5)
            ->get(['id', 'name']);

        // Contracts ending in the next 6 months forecast
        $contractsByMonth = [];
        for ($i = 0; $i <= 5; $i++) {
            $month = now()->addMonths($i);
            $contractsByMonth[] = [
                'month' => $month->format('Y-m'),
                'label' => $month->format('M Y'),
                'count' => Contract::whereYear('contract_end_date', $month->year)
                    ->whereMonth('contract_end_date', $month->month)
                    ->count(),
            ];
        }

        // ─── 3. VESSEL STATS ──────────────────────────────────────────────────
        $totalVessels  = Vessel::count();
        $activeVessels = Vessel::whereHas('contracts', fn ($q) => $q
            ->where('contract_start_date', '<=', now())
            ->where('contract_end_date', '>=', now())
        )->count();

        // ─── 4. DOCUMENT APPROVALS ────────────────────────────────────────────
        $pendingEmploymentDocs = EmploymentDocumentUpdate::where('status', 'pending')->count();
        $pendingTravelDocs     = TravelDocumentUpdate::where('status', 'pending')->count();
        $pendingCertificates   = CrewCertificateUpdate::where('status', 'pending')->count();
        $totalPendingDocuments = $pendingEmploymentDocs + $pendingTravelDocs + $pendingCertificates;

        // Expiring travel documents (next 30 days)
        $expiringTravel30 = TravelDocument::whereNotNull('expiration_date')
            ->where('expiration_date', '>=', now())
            ->where('expiration_date', '<=', now()->addDays(30))
            ->count();

        // Expiring certificates (next 30 days)
        $expiringCerts30 = CrewCertificate::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->count();

        // Recent 5 pending employment doc updates for the feed
        $recentPendingEmployment = EmploymentDocumentUpdate::with('userProfile')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($item) => [
                'id'         => $item->id,
                'crew_name'  => $item->userProfile?->full_name ?? 'Unknown',
                'created_at' => $item->created_at?->diffForHumans() ?? '—',
            ]);

        // Recent 5 pending travel doc updates for the feed
        $recentPendingTravel = TravelDocumentUpdate::with('userProfile')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($item) => [
                'id'         => $item->id,
                'crew_name'  => $item->userProfile?->full_name ?? 'Unknown',
                'created_at' => $item->created_at?->diffForHumans() ?? '—',
            ]);

        // ─── 5. APPOINTMENTS ──────────────────────────────────────────────────
        $todayAppointments = Appointment::whereDate('date', today())->count();
        $thisMonthAppointments = Appointment::whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->count();

        $appointmentsByStatus = Appointment::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // ─── 6. PROFILE UPDATE REQUESTS ───────────────────────────────────────
        $pendingProfileUpdates = ProfileUpdateRequest::where('status', 'pending')->count();

        // ─── 7. DEBRIEFING FORMS ──────────────────────────────────────────────
        $totalDebriefing = DebriefingForm::count();
        $debriefingByStatus = DebriefingForm::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // ─── 8. JOB DESCRIPTION REQUESTS ─────────────────────────────────────
        $jobDescByStatus = [];
        try {
            $jobDescByStatus = JobDescriptionRequest::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');
        } catch (\Throwable) {
            // table may not exist in all environments
        }

        // ─── BUILD RESPONSE ───────────────────────────────────────────────────
        return response()->json([
            'success' => true,
            'data' => [
                'crew' => [
                    'total'                  => $totalCrew,
                    'on_board'               => $onBoard,
                    'on_vacation'            => $onVacation,
                    'standby'                => $standby,
                    'new_hires'              => $newHires,
                    're_hires'               => $reHires,
                    'with_active_contracts'  => $crewWithActiveContracts,
                    'gender_distribution'    => $genderDistribution,
                    'registrations_by_month' => $registrationsByMonth,
                    'rank_distribution'      => $rankDistribution,
                ],
                'contracts' => [
                    'total'            => $totalContracts,
                    'active'           => $activeContracts,
                    'expired'          => $expiredContracts,
                    'expiring_30_days' => $expiring30,
                    'expiring_60_days' => $expiring60,
                    'expiring_90_days' => $expiring90,
                    'top_vessels'      => $topVessels,
                    'ending_by_month'  => $contractsByMonth,
                ],
                'vessels' => [
                    'total'  => $totalVessels,
                    'active' => $activeVessels,
                ],
                'documents' => [
                    'pending_employment'            => $pendingEmploymentDocs,
                    'pending_travel'                => $pendingTravelDocs,
                    'pending_certificates'          => $pendingCertificates,
                    'total_pending'                 => $totalPendingDocuments,
                    'expiring_travel_30_days'       => $expiringTravel30,
                    'expiring_certificates_30_days' => $expiringCerts30,
                    'recent_pending_employment'     => $recentPendingEmployment,
                    'recent_pending_travel'         => $recentPendingTravel,
                ],
                'appointments' => [
                    'today'      => $todayAppointments,
                    'this_month' => $thisMonthAppointments,
                    'by_status'  => $appointmentsByStatus,
                ],
                'profile_updates' => [
                    'pending' => $pendingProfileUpdates,
                ],
                'debriefing_forms' => [
                    'total'     => $totalDebriefing,
                    'by_status' => $debriefingByStatus,
                ],
                'job_description_requests' => [
                    'by_status' => $jobDescByStatus,
                ],
            ],
        ]);
    }
}
