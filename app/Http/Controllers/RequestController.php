<?php

namespace App\Http\Controllers;

use App\Models\Request as CertificateRequest;
use App\Models\Payment;
use App\Models\ActivityLog;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        $viewTracking = $request->input('view');

        $query = CertificateRequest::with(['resident', 'certificate', 'payment', 'processor', 'approver'])
            ->whereNull('archived_at');

        // Status Filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Search Filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('resident', function ($qr) use ($search) {
                      $qr->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->orderBy('requested_at', 'desc')->paginate(8);

        $viewedRequest = null;
        if ($viewTracking) {
            $viewedRequest = CertificateRequest::with(['resident', 'certificate', 'payment', 'processor', 'approver'])
                ->where('tracking_number', $viewTracking)
                ->first();
        }

        return view('admin.requests', compact('requests', 'viewedRequest', 'status', 'search'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:requests,id',
            'action'     => 'required|in:process,reject,approve,release,archive',
            'remarks'    => 'nullable|string|max:1000',
            'expected_release_date' => 'nullable|date',
        ]);

        $certReq = CertificateRequest::findOrFail($request->request_id);
        $action  = $request->action;
        $userId  = Auth::id();
        $msg     = '';
        $smsText = null;
        $previousStatus = $certReq->status;
        $firstName = $certReq->resident ? $certReq->resident->first_name : 'Resident';

        if ($action === 'process') {
            $certReq->update([
                'status'       => 'processing',
                'processed_by' => $userId,
                'processed_at' => now(),
            ]);
            $msg = 'Request marked as processing.';
            $smsText = "Hi {$firstName}, your document request ({$certReq->tracking_number}) is now being processed by Barangay Pili. We will notify you once it is ready.";

        } elseif ($action === 'reject') {
            $certReq->update([
                'status'       => 'rejected',
                'remarks'      => $request->remarks,
                'processed_by' => $userId,
                'processed_at' => now(),
            ]);
            $msg = 'Request rejected.';
            $reasonText = $request->remarks ? ' Reason: ' . Str::limit($request->remarks, 100) : '';
            $smsText = "Hi {$firstName}, your document request ({$certReq->tracking_number}) has been rejected.{$reasonText} For inquiries, please visit the Barangay Hall.";

        } elseif ($action === 'approve') {
            if (!$request->expected_release_date) {
                return redirect()->back()->withErrors(['expected_release_date' => 'Release date is required when approving a request.']);
            }
            
            $certReq->update([
                'status'                => 'approved',
                'approved_by'           => $userId,
                'approved_at'           => now(),
                'remarks'               => $request->remarks,
                'expected_release_date' => $request->expected_release_date,
            ]);
            if (!$certReq->processed_by) {
                $certReq->update([
                    'processed_by' => $userId,
                    'processed_at' => now(),
                ]);
            }
            if ($certReq->payment && $certReq->payment->payment_method === 'gcash') {
                $certReq->payment->update([
                    'payment_status' => 'paid',
                    'paid_at'        => now(),
                    'received_by'    => $userId,
                ]);
            }
            $msg = 'Request approved successfully.';
            $releaseFormatted = \Carbon\Carbon::parse($request->expected_release_date)->format('M d, Y h:i A');
            $remarksText = $request->remarks ? ' Remarks: ' . Str::limit($request->remarks, 100) : '';
            $smsText = "Hi {$firstName}, your request ({$certReq->tracking_number}) is APPROVED and will be ready for release by {$releaseFormatted}.{$remarksText} Pls bring valid ID.";

        } elseif ($action === 'release') {
            $certReq->update([
                'status'      => 'released',
                'released_at' => now(),
            ]);
            if ($certReq->payment) {
                $certReq->payment->update([
                    'payment_status' => 'paid',
                    'paid_at'        => now(),
                    'received_by'    => $userId,
                ]);
            }
            $msg = 'Request released and marked as paid.';
            $smsText = "Hi {$firstName}, your document ({$certReq->tracking_number}) has been successfully released. Thank you! - Barangay Pili";

        } elseif ($action === 'archive') {
            $certReq->update([
                'archived_at' => now(),
                'archived_by' => $userId,
            ]);
            $msg = 'Request archived safely. You can restore it from Archive.';
        }

        // ── Send SMS Notification ─────────────────────────────────────────────
        if ($smsText && $previousStatus !== $certReq->status) {
            SmsService::notifyResident(
                $certReq->resident,
                $smsText,
                "certificate request {$certReq->tracking_number}"
            );
        }

        ActivityLog::log(
            strtoupper($action) . '_REQUEST',
            'Requests',
            "Performed action '{$action}' on request {$certReq->tracking_number}"
        );

        return redirect()->route('admin.requests')->with('success', $msg);
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'request_id'     => 'required|exists:requests,id',
            'amount'         => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash',
            'payment_status' => 'required|in:paid,unpaid,waived',
        ]);

        $certReq = CertificateRequest::findOrFail($request->request_id);
        $previousPaymentStatus = $certReq->payment ? $certReq->payment->payment_status : null;

        if (!$certReq->payment) {
            Payment::create([
                'request_id'     => $certReq->id,
                'amount'         => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'paid_at'        => ($request->payment_status === 'paid') ? now() : null,
                'received_by'    => Auth::id(),
            ]);
        } else {
            $certReq->payment->update([
                'amount'         => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'paid_at'        => ($request->payment_status === 'paid') ? now() : null,
                'received_by'    => Auth::id(),
            ]);
        }

        if ($previousPaymentStatus !== $request->payment_status) {
            $firstName = $certReq->resident ? $certReq->resident->first_name : 'Resident';
            $amount = number_format((float) $request->amount, 2);

            if ($request->payment_status === 'paid') {
                $smsText = "Hi {$firstName}, payment of PHP {$amount} for document request ({$certReq->tracking_number}) has been recorded as PAID. - Barangay Pili";
            } elseif ($request->payment_status === 'waived') {
                $smsText = "Hi {$firstName}, the payment for document request ({$certReq->tracking_number}) has been WAIVED. No payment is required. - Barangay Pili";
            } else {
                $smsText = "Hi {$firstName}, the payment for document request ({$certReq->tracking_number}) is marked UNPAID. Please visit the Barangay Hall for assistance.";
            }

            SmsService::notifyResident(
                $certReq->resident,
                $smsText,
                "payment for certificate request {$certReq->tracking_number}"
            );
        }

        ActivityLog::log(
            'PROCESS_PAYMENT',
            'Payments',
            "Processed payment for request {$certReq->tracking_number}. Status: {$request->payment_status}"
        );

        return back()->with('success', 'Payment details updated successfully.');
    }
}
