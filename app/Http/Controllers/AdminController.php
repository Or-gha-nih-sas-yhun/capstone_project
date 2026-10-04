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

class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. Fetch Stats
        $stats = [
            'residents' => Resident::where('status', 'active')->whereNull('archived_at')->count(),
            'total_req' => CertificateRequest::count(),
            'pending' => CertificateRequest::where('status', 'pending')->count(),
            'approved' => CertificateRequest::where('status', 'approved')->count(),
            'released' => CertificateRequest::where('status', 'released')->count(),
            'revenue' => Payment::where('payment_status', 'paid')->sum('amount'),
            'pending_residents' => Resident::where('status', 'active')
                ->whereNull('archived_at')
                ->whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->count()
        ];

        // 2. Monthly Requests (last 6 months)
        $monthlyDataQuery = CertificateRequest::select(
            DB::raw("DATE_FORMAT(requested_at, '%b') as month"),
            DB::raw("COUNT(*) as total"),
            DB::raw("DATE_FORMAT(requested_at, '%Y-%m') as sort_key")
        )
        ->where('requested_at', '>=', now()->subMonths(6))
        ->groupBy('month', 'sort_key')
        ->orderBy('sort_key', 'asc')
        ->get();

        $monthly = [
            'labels' => $monthlyDataQuery->pluck('month')->toArray(),
            'data' => $monthlyDataQuery->pluck('total')->toArray()
        ];

        // 3. Requests by Certificate Type
        $byTypeDataQuery = Certificate::withCount(['requests'])
            ->where('status', 'active')
            ->orderBy('requests_count', 'desc')
            ->get();

        $byType = [
            'labels' => $byTypeDataQuery->pluck('name')->toArray(),
            'data' => $byTypeDataQuery->pluck('requests_count')->toArray()
        ];

        // 4. Recent Requests (limit 5)
        $recentRequests = CertificateRequest::with(['resident', 'certificate'])
            ->orderBy('requested_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'monthly', 'byType', 'recentRequests'));
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
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->email = $request->email;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/uploads/users'), $filename);

            // Delete old photo
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
        $month = $request->input('month', date('Y-m'));
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $from = $start . ' 00:00:00';
        $to = $end . ' 23:59:59';

        $transactions = CertificateRequest::with(['resident', 'certificate', 'payment'])
            ->whereNull('archived_at')
            ->whereBetween('requested_at', [$from, $to])
            ->orderBy('requested_at', 'desc')
            ->get();

        $byCert = Certificate::select(
            'certificates.name',
            'certificates.category',
            DB::raw('COUNT(requests.id) as total'),
            DB::raw("SUM(CASE WHEN requests.status='approved' THEN 1 ELSE 0 END) as approved"),
            DB::raw("SUM(CASE WHEN requests.status='released' THEN 1 ELSE 0 END) as released"),
            DB::raw("COALESCE(SUM(CASE WHEN payments.payment_status='paid' THEN payments.amount ELSE 0 END), 0) as collections")
        )
        ->leftJoin('requests', function ($join) use ($from, $to) {
            $join->on('requests.certificate_id', '=', 'certificates.id')
                 ->whereNull('requests.archived_at')
                 ->whereBetween('requests.requested_at', [$from, $to]);
        })
        ->leftJoin('payments', 'payments.request_id', '=', 'requests.id')
        ->groupBy('certificates.id', 'certificates.name', 'certificates.category')
        ->having('total', '>', 0)
        ->orderBy('certificates.category')
        ->orderBy('certificates.name')
        ->get();

        $totalAmount = 0;
        foreach ($transactions as $t) {
            if ($t->payment && $t->payment->payment_status === 'paid') {
                $totalAmount += (float)$t->payment->amount;
            }
        }

        if ($request->input('export') === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename=transactions_report_' . $month . '.csv',
            ];
            
            $callback = function () use ($transactions) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Tracking No.', 'Resident', 'Certificate', 'Date Filed', 'Status', 'Payment', 'Amount']);
                foreach ($transactions as $row) {
                    fputcsv($file, [
                        $row->tracking_number,
                        $row->resident->full_name,
                        $row->certificate->name,
                        $row->requested_at,
                        $row->status,
                        $row->payment->payment_status ?? 'unpaid',
                        $row->payment ? $row->payment->amount : $row->certificate->fee
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        $captain = Official::getCaptain();
        $captainName = $captain ? $captain->name : 'PUNONG BARANGAY';

        return view('admin.reports', compact('transactions', 'byCert', 'totalAmount', 'month', 'start', 'captainName'));
    }

    public function activityLogs()
    {
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(8);

        return view('admin.activity_logs', compact('logs'));
    }
}
