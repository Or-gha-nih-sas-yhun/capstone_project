<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResidentManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'active');
        $editId = $request->input('edit');

        $query = Resident::whereNull('archived_at');

        if ($status === 'pending') {
            $query->whereHas('user', function ($q) {
                $q->where('status', 'inactive');
            });
        } else {
            // Active residents whose user is active or don't have user account
            $query->where(function ($q) {
                $q->whereHas('user', function ($qu) {
                    $qu->where('status', 'active');
                })->orWhereDoesntHave('user');
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $residents = $query->orderBy('last_name')->orderBy('first_name')->paginate(8);

        $editResident = null;
        if ($editId) {
            $editResident = Resident::findOrFail($editId);
        }

        $pendingCount = Resident::whereNull('archived_at')
            ->whereHas('user', function ($q) {
                $q->where('status', 'inactive');
            })->count();

        return view('admin.residents', compact('residents', 'editResident', 'status', 'search', 'pendingCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'resident_id' => 'required|integer',
            'first_name' => 'required|string|regex:/^[a-zA-Z\s\-\.,]+$/',
            'last_name' => 'required|string|regex:/^[a-zA-Z\s\-\.,]+$/',
            'middle_name' => 'nullable|string|regex:/^[a-zA-Z\s\-\.,]+$/',
            'gender' => 'required|in:Male,Female,Other',
            'birthdate' => 'required|date',
            'civil_status' => 'required|in:Single,Married,Widowed,Separated',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'required|string|max:255',
            'purok' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:150',
            'voter_status' => 'required|in:Registered,Not Registered',
            'years_of_residency' => 'required|integer|min:0',
        ], [
            'first_name.regex' => 'First name must contain only letters, spaces, hyphens, and periods.',
            'last_name.regex' => 'Last name must contain only letters, spaces, hyphens, and periods.',
            'middle_name.regex' => 'Middle name must contain only letters, spaces, hyphens, and periods.',
        ]);

        $data = $request->only([
            'first_name', 'middle_name', 'last_name', 'gender', 'birthdate',
            'civil_status', 'contact_number', 'email', 'address', 'purok',
            'occupation', 'voter_status', 'years_of_residency'
        ]);

        $id = $request->resident_id;

        if ($id > 0) {
            $resident = Resident::findOrFail($id);
            $resident->update($data);
            ActivityLog::log('UPDATE_RESIDENT', 'Residents', "Updated resident ID $id");
            $msg = 'Resident updated successfully.';
        } else {
            $data['status'] = 'active';
            Resident::create($data);
            ActivityLog::log('ADD_RESIDENT', 'Residents', 'Added new resident');
            $msg = 'Resident added successfully.';
        }

        return redirect()->route('admin.residents')->with('success', $msg);
    }

    public function approve($id)
    {
        $resident = Resident::findOrFail($id);
        if ($resident->user) {
            $previousStatus = $resident->user->status;
            $resident->user->update(['status' => 'active']);

            if ($previousStatus !== 'active') {
                SmsService::notifyResident(
                    $resident,
                    "Hi {$resident->first_name}, your Barangay Pili resident account has been APPROVED. You may now log in to the resident portal.",
                    "resident account approval {$resident->id}"
                );
            }

            ActivityLog::log('APPROVE_RESIDENT', 'Residents', "Approved resident user linked to resident ID $id");
            return redirect()->route('admin.residents', ['status' => 'pending'])->with('success', 'Resident registry approved successfully.');
        }
        return redirect()->route('admin.residents')->with('error', 'No user account linked to this resident.');
    }

    public function reject($id)
    {
        $resident = Resident::findOrFail($id);
        $userId = Auth::id();
        $wasAlreadyRejected = $resident->archived_at !== null;

        $resident->update([
            'status' => 'inactive',
            'archived_at' => now(),
            'archived_by' => $userId
        ]);

        if ($resident->user) {
            $resident->user->update([
                'status' => 'inactive',
                'archived_at' => now(),
                'archived_by' => $userId
            ]);
        }

        if (!$wasAlreadyRejected) {
            SmsService::notifyResident(
                $resident,
                "Hi {$resident->first_name}, your Barangay Pili resident registration was not approved. Please visit the Barangay Hall for verification or assistance.",
                "resident registration rejection {$resident->id}"
            );
        }

        ActivityLog::log('REJECT_RESIDENT', 'Residents', "Rejected resident ID $id");

        return redirect()->route('admin.residents', ['status' => 'pending'])->with('success', 'Resident registration rejected.');
    }

    public function delete($id)
    {
        $resident = Resident::findOrFail($id);
        $userId = Auth::id();

        $resident->update([
            'status' => 'inactive',
            'archived_at' => now(),
            'archived_by' => $userId
        ]);

        if ($resident->user) {
            $resident->user->update([
                'status' => 'inactive',
                'archived_at' => now(),
                'archived_by' => $userId
            ]);
        }

        ActivityLog::log('ARCHIVE_RESIDENT', 'Residents', "Archived resident ID $id");

        return redirect()->route('admin.residents')->with('success', 'Resident archived safely. You can restore it from Archive.');
    }
}
