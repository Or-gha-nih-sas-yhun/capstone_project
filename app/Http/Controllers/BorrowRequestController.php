<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use App\Models\ActivityLog;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BorrowRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = BorrowRequest::with(['resident', 'approver']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('resident', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $borrows = $query->orderBy('created_at', 'desc')->paginate(8);

        return view('admin.borrows', compact('borrows', 'status', 'search'));
    }

    public function residentIndex()
    {
        $resident = Auth::user()->resident;
        if (!$resident) {
            return redirect()->route('login')->with('error', 'Profile not found.');
        }

        $borrows = BorrowRequest::where('resident_id', $resident->id)
            ->orderBy('created_at', 'desc')
            ->paginate(8);

        return view('resident.borrows', compact('borrows', 'resident'));
    }

    public function store(Request $request)
    {
        $itemType = $request->input('item_type');
        if ($itemType === 'tent') {
            $request->merge(['chair_quantity' => 0, 'table_quantity' => 0]);
        } elseif ($itemType === 'chair') {
            $request->merge(['tent_quantity' => 0, 'table_quantity' => 0]);
        } elseif ($itemType === 'table') {
            $request->merge(['tent_quantity' => 0, 'chair_quantity' => 0]);
        } elseif ($itemType === 'both') {
            $request->merge(['table_quantity' => 0]);
        }

        $request->validate([
            'item_type' => 'required|in:tent,chair,table,both,all',
            'tent_quantity' => 'required|integer|min:0|max:5',
            'chair_quantity' => 'required|integer|min:0|max:50',
            'table_quantity' => 'required|integer|min:0|max:25',
            'borrow_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after_or_equal:borrow_date',
            'purpose' => 'required|string|max:1000',
            'verification_document' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ], [
            'tent_quantity.max' => 'You cannot borrow more than 5 tents.',
            'chair_quantity.max' => 'You cannot borrow more than 50 chairs.',
            'table_quantity.max' => 'You cannot borrow more than 25 tables.',
        ]);

        $resident = Auth::user()->resident;
        if (!$resident) {
            return redirect()->route('login')->with('error', 'Profile not found.');
        }

        // Must borrow at least 1 item
        if ($request->tent_quantity == 0 && $request->chair_quantity == 0 && $request->table_quantity == 0) {
            return back()->withErrors(['item_type' => 'You must borrow at least 1 tent, 1 chair, or 1 table.'])->withInput();
        }

        // Handle upload
        $file = $request->file('verification_document');
        $filename = 'verify_' . time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
        
        // Ensure folder exists
        $uploadPath = public_path('assets/uploads/borrow_documents');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $filename);

        BorrowRequest::create([
            'resident_id' => $resident->id,
            'item_type' => $request->item_type,
            'tent_quantity' => $request->tent_quantity,
            'chair_quantity' => $request->chair_quantity,
            'table_quantity' => $request->table_quantity,
            'borrow_date' => $request->borrow_date,
            'return_date' => $request->return_date,
            'purpose' => $request->purpose,
            'verification_document' => $filename,
            'status' => 'pending',
        ]);

        ActivityLog::log('BORROW_REQUEST', 'Borrow', "Submitted request to borrow equipment on {$request->borrow_date}");

        return redirect()->route('resident.borrows')->with('success', 'Equipment borrow request submitted successfully. Pending administrator verification.');
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'borrow_id' => 'required|exists:borrow_requests,id',
            'status' => 'required|in:approved,rejected,returned',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $borrow = BorrowRequest::with('resident')->findOrFail($request->borrow_id);
        $previousStatus = $borrow->status;
        
        $updateData = [
            'status' => $request->status,
            'remarks' => $request->remarks,
        ];

        if ($request->status === 'approved') {
            $updateData['approved_by'] = Auth::id();
            $updateData['approved_at'] = now();
        }

        $borrow->update($updateData);

        if ($previousStatus !== $borrow->status) {
            $firstName = $borrow->resident ? $borrow->resident->first_name : 'Resident';

            if ($borrow->status === 'approved') {
                $borrowDate = $borrow->borrow_date->format('M d, Y');
                $smsText = "Hi {$firstName}, your equipment borrow request #{$borrow->id} for {$borrowDate} has been APPROVED. Please coordinate pickup with the Barangay Hall and bring a valid ID.";
            } elseif ($borrow->status === 'rejected') {
                $reasonText = $borrow->remarks ? ' Reason: ' . Str::limit($borrow->remarks, 100) : '';
                $smsText = "Hi {$firstName}, your equipment borrow request #{$borrow->id} has been REJECTED.{$reasonText} Please contact the Barangay Hall for assistance.";
            } else {
                $smsText = "Hi {$firstName}, your equipment borrow request #{$borrow->id} has been marked RETURNED and completed. Thank you! - " . sms_signature();
            }

            SmsService::notifyResident(
                $borrow->resident,
                $smsText,
                "borrow request {$borrow->id}"
            );
        }

        ActivityLog::log(
            strtoupper($request->status) . '_BORROW',
            'Borrow',
            "Marked borrow request ID {$borrow->id} as {$request->status}"
        );

        return back()->with('success', "Borrow request status updated to {$request->status} successfully.");
    }

    public function checkAvailability(Request $request)
    {
        $borrowDate = $request->input('borrow_date');
        $returnDate = $request->input('return_date');

        if (!$borrowDate || !$returnDate) {
            return response()->json([
                'tent' => 5,
                'chair' => 50,
                'table' => 25
            ]);
        }

        $overlappingRequests = BorrowRequest::whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($borrowDate, $returnDate) {
                $query->whereBetween('borrow_date', [$borrowDate, $returnDate])
                      ->orWhereBetween('return_date', [$borrowDate, $returnDate])
                      ->orWhere(function ($q) use ($borrowDate, $returnDate) {
                          $q->where('borrow_date', '<=', $borrowDate)
                            ->where('return_date', '>=', $returnDate);
                      });
            })
            ->get();

        $usedTents = $overlappingRequests->sum('tent_quantity');
        $usedChairs = $overlappingRequests->sum('chair_quantity');
        $usedTables = $overlappingRequests->sum('table_quantity');

        return response()->json([
            'tent' => max(0, 5 - $usedTents),
            'chair' => max(0, 50 - $usedChairs),
            'table' => max(0, 25 - $usedTables)
        ]);
    }
}
