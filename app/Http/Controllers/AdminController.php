<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Resident;
use App\Models\Request as CertificateRequest;
use App\Models\Payment;
use App\Models\Certificate;
use App\Models\ActivityLog;
use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $sixtyYearsAgo  = now()->subYears(60)->format('Y-m-d');
        $thisMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd   = now()->subMonth()->endOfMonth();

        // ── Core Stats ──────────────────────────────────────────
        $stats = [
            'residents'         => Resident::where('status', 'active')->whereNull('archived_at')->count(),
            'total_req'         => CertificateRequest::count(),
            'pending'           => CertificateRequest::where('status', 'pending')->count(),
            'approved'          => CertificateRequest::where('status', 'approved')->count(),
            'released'          => CertificateRequest::where('status', 'released')->count(),
            'rejected'          => CertificateRequest::where('status', 'rejected')->count(),
            'revenue'           => Payment::where('payment_status', 'paid')->sum('amount'),
            'month_revenue'     => Payment::where('payment_status', 'paid')
                                    ->where('created_at', '>=', $thisMonthStart)->sum('amount'),
            'last_month_revenue'=> Payment::where('payment_status', 'paid')
                                    ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('amount'),
            'month_requests'    => CertificateRequest::where('requested_at', '>=', $thisMonthStart)->count(),
            'today_requests'    => CertificateRequest::whereDate('requested_at', today())->count(),
            'today_released'    => CertificateRequest::where('status', 'released')->whereDate('updated_at', today())->count(),
            'pwd'               => Resident::whereNull('archived_at')->where('is_pwd', true)->count(),
            'senior'            => Resident::whereNull('archived_at')->where('birthdate', '<=', $sixtyYearsAgo)->count(),
            'single_parent'     => Resident::whereNull('archived_at')->where('is_single_parent', true)->count(),
            'pending_residents' => Resident::where('status', 'active')
                                    ->whereNull('archived_at')
                                    ->whereHas('user', fn($q) => $q->where('status', 'inactive'))
                                    ->count(),
        ];

        $stats['revenue_trend'] = $stats['last_month_revenue'] > 0
            ? round((($stats['month_revenue'] - $stats['last_month_revenue']) / $stats['last_month_revenue']) * 100, 1)
            : ($stats['month_revenue'] > 0 ? 100 : 0);

        $stats['completion_rate'] = $stats['total_req'] > 0
            ? round(($stats['released'] / $stats['total_req']) * 100, 1)
            : 0;

        // ── Monthly Requests (last 6 months) ────────────────────
        $monthlyDataQuery = CertificateRequest::select(
            DB::raw("DATE_FORMAT(requested_at, '%b') as month"),
            DB::raw("COUNT(*) as total"),
            DB::raw("SUM(CASE WHEN status='released' THEN 1 ELSE 0 END) as released"),
            DB::raw("DATE_FORMAT(requested_at, '%Y-%m') as sort_key")
        )
        ->where('requested_at', '>=', now()->subMonths(6))
        ->groupBy('month', 'sort_key')
        ->orderBy('sort_key', 'asc')
        ->get();

        $monthly = [
            'labels'   => $monthlyDataQuery->pluck('month')->toArray(),
            'data'     => $monthlyDataQuery->pluck('total')->toArray(),
            'released' => $monthlyDataQuery->pluck('released')->toArray(),
        ];

        // ── Requests by Certificate Type ─────────────────────────
        $byTypeDataQuery = Certificate::withCount(['requests'])
            ->where('status', 'active')
            ->orderBy('requests_count', 'desc')
            ->get();

        $byType = [
            'labels' => $byTypeDataQuery->pluck('name')->toArray(),
            'data'   => $byTypeDataQuery->pluck('requests_count')->toArray(),
        ];

        // ── Monthly Revenue (last 6 months) ──────────────────────
        $revenueQuery = Payment::select(
            DB::raw("DATE_FORMAT(created_at, '%b') as month"),
            DB::raw("SUM(amount) as total"),
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as sort_key")
        )
        ->where('payment_status', 'paid')
        ->where('created_at', '>=', now()->subMonths(6))
        ->groupBy('month', 'sort_key')
        ->orderBy('sort_key', 'asc')
        ->get();

        $revenueChart = [
            'labels' => $revenueQuery->pluck('month')->toArray(),
            'data'   => $revenueQuery->pluck('total')->toArray(),
        ];

        // ── Demographics ─────────────────────────────────────────
        $ageGroups = [
            'Children (0-12)'     => Resident::whereNull('archived_at')->where('status', 'active')
                ->where('birthdate', '>', now()->subYears(13)->format('Y-m-d'))->count(),
            'Youth (13-17)'       => Resident::whereNull('archived_at')->where('status', 'active')
                ->whereBetween('birthdate', [now()->subYears(18)->format('Y-m-d'), now()->subYears(13)->format('Y-m-d')])->count(),
            'Young Adult (18-35)' => Resident::whereNull('archived_at')->where('status', 'active')
                ->whereBetween('birthdate', [now()->subYears(36)->format('Y-m-d'), now()->subYears(18)->format('Y-m-d')])->count(),
            'Adult (36-59)'       => Resident::whereNull('archived_at')->where('status', 'active')
                ->whereBetween('birthdate', [now()->subYears(60)->format('Y-m-d'), now()->subYears(36)->format('Y-m-d')])->count(),
            'Senior (60+)'        => $stats['senior'],
        ];

        $genderBreakdown = Resident::whereNull('archived_at')->where('status', 'active')
            ->select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')->pluck('total', 'gender')->toArray();

        $demographics = [
            'pwd'           => $stats['pwd'],
            'senior'        => $stats['senior'],
            'single_parent' => $stats['single_parent'],
            'total'         => $stats['residents'],
            'gender'        => $genderBreakdown,
            'age_groups'    => $ageGroups,
        ];

        // ── Top Cert ─────────────────────────────────────────────
        $topCert = Certificate::withCount('requests')
            ->orderBy('requests_count', 'desc')
            ->first();

        // ── Recent Requests ───────────────────────────────────────
        $recentRequests = CertificateRequest::with(['resident', 'certificate'])
            ->orderBy('requested_at', 'desc')
            ->limit(8)
            ->get();

        // ── Recent Activity ───────────────────────────────────────
        $recentActivity = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'monthly', 'byType', 'recentRequests',
            'revenueChart', 'topCert', 'recentActivity', 'demographics'
        ));
    }

    public function profileForm()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'photo'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->email = $request->email;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $file     = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/uploads/users'), $filename);

            if ($user->photo && file_exists(public_path('assets/uploads/' . $user->photo))) {
                @unlink(public_path('assets/uploads/' . $user->photo));
            }

            $user->photo = 'users/' . $filename;
        }

        $user->save();

        ActivityLog::log('UPDATE_PROFILE', 'Profile', 'Updated administrator profile credentials');

        return back()->with('success', 'Profile updated successfully.');
    }

    public function reports(Request $request)
    {
        $period              = $request->input('period', 'monthly');
        $statusFilter        = $request->input('status', 'all');
        $certificateFilter   = $request->input('certificate_id', 'all');
        $purokFilter         = $request->input('purok');
        $paymentStatusFilter = $request->input('payment_status', 'all');

        $now            = now();
        $dateParam      = $request->input('date', $now->toDateString());
        $weekDateParam  = $request->input('week_date', $now->toDateString());
        $monthParam     = $request->input('month', $now->format('Y-m'));
        $startDateParam = $request->input('start_date', $now->startOfMonth()->toDateString());
        $endDateParam   = $request->input('end_date', $now->toDateString());

        if ($period === 'daily') {
            $parsedDate    = Carbon::parse($dateParam);
            $from          = $parsedDate->copy()->startOfDay();
            $to            = $parsedDate->copy()->endOfDay();
            $reportTitle   = 'DAILY DOCUMENT ISSUANCE & TRANSACTIONS REPORT';
            $reportSubtitle= $parsedDate->format('F d, Y') . ' (' . $parsedDate->format('l') . ')';
            $selectedDate  = $parsedDate->toDateString();
        } elseif ($period === 'weekly') {
            $parsedWeek    = Carbon::parse($weekDateParam);
            $from          = $parsedWeek->copy()->startOfWeek();
            $to            = $parsedWeek->copy()->endOfWeek();
            $reportTitle   = 'WEEKLY DOCUMENT ISSUANCE & TRANSACTIONS REPORT';
            $reportSubtitle= $from->format('M d, Y') . ' – ' . $to->format('M d, Y');
            $selectedDate  = $parsedWeek->toDateString();
        } elseif ($period === 'custom') {
            $from          = Carbon::parse($startDateParam)->startOfDay();
            $to            = Carbon::parse($endDateParam)->endOfDay();
            $reportTitle   = 'CUSTOM PERIOD ISSUANCE & TRANSACTIONS REPORT';
            $reportSubtitle= $from->format('M d, Y') . ' – ' . $to->format('M d, Y');
            $selectedDate  = $now->toDateString();
        } else {
            $period        = 'monthly';
            $parsedMonth   = Carbon::parse($monthParam . '-01');
            $from          = $parsedMonth->copy()->startOfMonth();
            $to            = $parsedMonth->copy()->endOfMonth();
            $reportTitle   = 'MONTHLY DOCUMENT ISSUANCE & TRANSACTIONS REPORT';
            $reportSubtitle= $parsedMonth->format('F Y');
            $selectedDate  = $now->toDateString();
        }

        $baseQ = CertificateRequest::whereNull('archived_at')->whereBetween('requested_at', [$from, $to]);

        $summary = [
            'total_requests'    => (clone $baseQ)->count(),
            'total_issued'      => (clone $baseQ)->where('status', 'released')->count(),
            'total_approved'    => (clone $baseQ)->where('status', 'approved')->count(),
            'total_pending'     => (clone $baseQ)->where('status', 'pending')->count(),
            'total_processing'  => (clone $baseQ)->where('status', 'processing')->count(),
            'total_rejected'    => (clone $baseQ)->where('status', 'rejected')->count(),
            'total_collections' => Payment::whereHas('request', fn($q) =>
                $q->whereNull('archived_at')->whereBetween('requested_at', [$from, $to])
            )->where('payment_status', 'paid')->sum('amount'),
        ];
        $summary['issuance_rate'] = $summary['total_requests'] > 0
            ? round(($summary['total_issued'] / $summary['total_requests']) * 100, 1) : 0;

        $byCert = Certificate::select(
            'certificates.id', 'certificates.name', 'certificates.category', 'certificates.fee',
            DB::raw('COUNT(requests.id) as total_requests'),
            DB::raw("SUM(CASE WHEN requests.status='approved' THEN 1 ELSE 0 END) as approved_count"),
            DB::raw("SUM(CASE WHEN requests.status='released' THEN 1 ELSE 0 END) as issued_count"),
            DB::raw("SUM(CASE WHEN requests.status='pending'  THEN 1 ELSE 0 END) as pending_count"),
            DB::raw("COALESCE(SUM(CASE WHEN payments.payment_status='paid' THEN payments.amount ELSE 0 END),0) as collections")
        )
        ->leftJoin('requests', function ($j) use ($from, $to) {
            $j->on('requests.certificate_id', '=', 'certificates.id')
              ->whereNull('requests.archived_at')
              ->whereBetween('requests.requested_at', [$from, $to]);
        })
        ->leftJoin('payments', 'payments.request_id', '=', 'requests.id')
        ->whereNull('certificates.archived_at')
        ->groupBy('certificates.id', 'certificates.name', 'certificates.category', 'certificates.fee')
        ->having('total_requests', '>', 0)
        ->orderByDesc('issued_count')
        ->get();

        foreach ($byCert as $cert) {
            $cert->share_percentage = $summary['total_issued'] > 0
                ? round(($cert->issued_count / $summary['total_issued']) * 100, 1) : 0;
        }

        $tQ = CertificateRequest::with(['resident', 'certificate', 'payment', 'processor', 'approver'])
            ->whereNull('archived_at')
            ->whereBetween('requested_at', [$from, $to]);

        if ($statusFilter && $statusFilter !== 'all')        $tQ->where('status', $statusFilter);
        if ($certificateFilter && $certificateFilter !== 'all') $tQ->where('certificate_id', $certificateFilter);
        if ($purokFilter) $tQ->whereHas('resident', fn($q) => $q->where('purok', $purokFilter));
        if ($paymentStatusFilter && $paymentStatusFilter !== 'all')
            $tQ->whereHas('payment', fn($q) => $q->where('payment_status', $paymentStatusFilter));

        $transactions = $tQ->orderBy('requested_at', 'desc')->get();

        $filteredCollections = 0;
        foreach ($transactions as $t) {
            if ($t->payment && $t->payment->payment_status === 'paid') {
                $filteredCollections += (float) $t->payment->amount;
            }
        }

        if ($request->input('export') === 'csv') {
            $filename = 'issuance_report_' . $period . '_' . $from->format('Ymd') . '_to_' . $to->format('Ymd') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            $callback = function () use ($transactions) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($file, ['Tracking No.','Resident Name','Purok','Gender','Civil Status','Contact',
                    'Certificate / Clearance','Category','Purpose','Date Filed','Date Approved',
                    'Date Issued / Released','Status','Payment Status','Payment Method','Amount (PHP)','Approved By']);
                foreach ($transactions as $row) {
                    fputcsv($file, [
                        $row->tracking_number,
                        $row->resident ? $row->resident->full_name : 'N/A',
                        $row->resident ? ($row->resident->purok ?: 'N/A') : 'N/A',
                        $row->resident ? ($row->resident->gender ?: 'N/A') : 'N/A',
                        $row->resident ? ($row->resident->civil_status ?: 'N/A') : 'N/A',
                        $row->resident ? ($row->resident->phone_number ?? $row->resident->contact_number ?? 'N/A') : 'N/A',
                        $row->certificate ? $row->certificate->name : 'N/A',
                        $row->certificate ? ($row->certificate->category ?: 'General') : 'N/A',
                        $row->purpose ?: 'N/A',
                        $row->requested_at ? Carbon::parse($row->requested_at)->format('Y-m-d H:i') : '',
                        $row->approved_at  ? Carbon::parse($row->approved_at)->format('Y-m-d H:i')  : '',
                        $row->released_at  ? Carbon::parse($row->released_at)->format('Y-m-d H:i')  : '',
                        ucfirst($row->status),
                        ucfirst($row->payment->payment_status ?? 'unpaid'),
                        ucfirst($row->payment->payment_method  ?? 'cash'),
                        number_format($row->payment ? $row->payment->amount : ($row->certificate->fee ?? 0), 2, '.', ''),
                        $row->approver ? $row->approver->username : 'N/A',
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        $allCertificates = Certificate::whereNull('archived_at')->orderBy('name')->get();
        $puroks = Resident::select('purok')->whereNotNull('purok')->whereNull('archived_at')
            ->distinct()->orderBy('purok')->pluck('purok');

        $sixtyYearsAgo   = now()->subYears(60)->format('Y-m-d');
        $activeResidents = Resident::whereNull('archived_at');
        if ($purokFilter) $activeResidents->where('purok', $purokFilter);

        $demographics = [
            'total_registered'    => (clone $activeResidents)->count(),
            'total_pwd'           => (clone $activeResidents)->where('is_pwd', true)->count(),
            'total_senior'        => (clone $activeResidents)->where('birthdate', '<=', $sixtyYearsAgo)->count(),
            'total_single_parent' => (clone $activeResidents)->where('is_single_parent', true)->count(),
            'total_widowed'       => (clone $activeResidents)->where('civil_status', 'Widowed')->count(),
            'total_married'       => (clone $activeResidents)->where('civil_status', 'Married')->count(),
            'total_single'        => (clone $activeResidents)->where('civil_status', 'Single')->count(),
            'gender'              => (clone $activeResidents)->select('gender', DB::raw('count(*) as total'))
                                        ->groupBy('gender')->pluck('total', 'gender')->toArray(),
            'civil_status'        => (clone $activeResidents)->select('civil_status', DB::raw('count(*) as total'))
                                        ->groupBy('civil_status')->pluck('total', 'civil_status')->toArray(),
        ];

        $captain       = Official::getCaptain();
        $secretary     = Official::getSecretary();
        $captainName   = $captain   ? $captain->name   : 'PUNONG BARANGAY';
        $secretaryName = $secretary ? $secretary->name : 'BARANGAY SECRETARY';

        return view('admin.reports', compact(
            'period', 'reportTitle', 'reportSubtitle', 'from', 'to',
            'summary', 'byCert', 'transactions', 'filteredCollections',
            'allCertificates', 'puroks', 'demographics',
            'captainName', 'secretaryName',
            'statusFilter', 'certificateFilter', 'purokFilter', 'paymentStatusFilter',
            'dateParam', 'weekDateParam', 'monthParam', 'startDateParam', 'endDateParam'
        ));
    }

    public function activityLogs()
    {
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(8);

        return view('admin.activity_logs', compact('logs'));
    }
}