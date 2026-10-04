<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\ActivityLog;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        $method = $request->input('method', 'all');

        // Query builder
        $query = Payment::with(['receiver', 'request.resident', 'request.certificate']);

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('request', function ($qr) use ($search) {
                      $qr->where('tracking_number', 'like', "%{$search}%")
                        ->orWhereHas('resident', function ($qres) use ($search) {
                            $qres->where('first_name', 'like', "%{$search}%")
                                 ->orWhere('last_name', 'like', "%{$search}%");
                        });
                  });
            });
        }

        // Status filter
        if ($status !== 'all') {
            $query->where('payment_status', $status);
        }

        // Method filter
        if ($method !== 'all') {
            $query->where('payment_method', $method);
        }

        // Paginated payments
        $payments = $query->orderBy('updated_at', 'desc')->paginate(8);

        // Stats calculations
        $statsQuery = Payment::query();
        $stats = [
            'total_cash' => (clone $statsQuery)->where('payment_method', 'cash')->where('payment_status', 'paid')->sum('amount'),
            'total_paid' => (clone $statsQuery)->where('payment_status', 'paid')->sum('amount'),
            'total_pending' => (clone $statsQuery)->where('payment_status', 'unpaid')->sum('amount'),
        ];

        // Document-type inventory: count & revenue per certificate
        $inventory = \DB::table('payments')
            ->join('requests', 'payments.request_id', '=', 'requests.id')
            ->join('certificates', 'requests.certificate_id', '=', 'certificates.id')
            ->select(
                'certificates.name as cert_name',
                \DB::raw('COUNT(payments.id) as total_requests'),
                \DB::raw('SUM(CASE WHEN payments.payment_status = \'paid\' THEN 1 ELSE 0 END) as total_paid_count'),
                \DB::raw('SUM(CASE WHEN payments.payment_status = \'unpaid\' THEN 1 ELSE 0 END) as total_unpaid_count'),
                \DB::raw('SUM(CASE WHEN payments.payment_status = \'waived\' THEN 1 ELSE 0 END) as total_waived_count'),
                \DB::raw('SUM(CASE WHEN payments.payment_status = \'paid\' THEN payments.amount ELSE 0 END) as total_revenue'),
                'certificates.fee as unit_fee'
            )
            ->groupBy('certificates.id', 'certificates.name', 'certificates.fee')
            ->orderByDesc('total_revenue')
            ->get();

        return view('admin.payments', compact('payments', 'stats', 'search', 'status', 'method', 'inventory'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash',
            'payment_status' => 'required|in:paid,unpaid,waived',
        ]);

        $payment = Payment::with('request.resident')->findOrFail($request->payment_id);
        $previousPaymentStatus = $payment->payment_status;
        $payment->update([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'paid_at' => ($request->payment_status === 'paid') ? now() : null,
            'received_by' => Auth::id(),
        ]);

        // Also update request status if it is marked as paid and was pending/processing
        $certReq = $payment->request;
        $autoApproved = false;
        if ($certReq && $request->payment_status === 'paid' && in_array($certReq->status, ['pending', 'processing'], true)) {
            $certReq->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            $autoApproved = true;
        }

        if ($certReq && ($previousPaymentStatus !== $payment->payment_status || $autoApproved)) {
            $resident = $certReq->resident;
            $firstName = $resident ? $resident->first_name : 'Resident';
            $amount = number_format((float) $payment->amount, 2);

            if ($autoApproved) {
                $smsText = "Hi {$firstName}, payment of PHP {$amount} for document request ({$certReq->tracking_number}) is PAID. Your request is APPROVED and ready for pickup at the Barangay Hall. Bring a valid ID.";
            } elseif ($payment->payment_status === 'paid') {
                $smsText = "Hi {$firstName}, payment of PHP {$amount} for document request ({$certReq->tracking_number}) has been recorded as PAID. - Barangay Pili";
            } elseif ($payment->payment_status === 'waived') {
                $smsText = "Hi {$firstName}, the payment for document request ({$certReq->tracking_number}) has been WAIVED. No payment is required. - Barangay Pili";
            } else {
                $smsText = "Hi {$firstName}, the payment for document request ({$certReq->tracking_number}) is marked UNPAID. Please visit the Barangay Hall for assistance.";
            }

            SmsService::notifyResident(
                $resident,
                $smsText,
                "payment for certificate request {$certReq->tracking_number}"
            );
        }

        ActivityLog::log(
            'UPDATE_PAYMENT',
            'Payments',
            "Updated payment details. Status: {$request->payment_status}"
        );

        return back()->with('success', 'Payment details updated successfully.');
    }
}
