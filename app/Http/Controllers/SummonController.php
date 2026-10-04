<?php

namespace App\Http\Controllers;

use App\Models\Summon;
use App\Models\SummonHearing;
use App\Models\Resident;
use App\Models\ActivityLog;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SummonController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        $type = $request->input('case_type', 'all');

        $query = Summon::with(['complainantResident', 'respondentResident', 'hearings'])
            ->whereNull('archived_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                    ->orWhere('complainant_name', 'like', "%{$search}%")
                    ->orWhere('respondent_name', 'like', "%{$search}%")
                    ->orWhere('nature_of_complaint', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($type !== 'all') {
            $query->where('case_type', $type);
        }

        // Order by latest schedule, or latest updated
        $summons = $query->orderBy('created_at', 'desc')->paginate(8);
        $residents = Resident::where('status', 'active')->orderBy('last_name')->get();

        return view('admin.summons', compact('summons', 'residents', 'search', 'status', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'case_type' => 'required|in:blotter,summon',
            'complainant_name' => 'required|string|max:255',
            'complainant_contact' => 'nullable|string|max:100',
            'complainant_resident_id' => 'nullable|exists:residents,id',
            'respondent_name' => 'required|string|max:255',
            'respondent_contact' => 'nullable|string|max:100',
            'respondent_resident_id' => 'nullable|exists:residents,id',
            'complain_details' => 'required|string',
            'incident_date' => 'nullable|date',
            'incident_location' => 'nullable|string|max:255',
            'nature_of_complaint' => 'nullable|string|max:255',
            'schedule_date' => 'required_if:case_type,summon|nullable|date|after_or_equal:today',
        ]);

        $caseNumber = 'SUMMON-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $summon = Summon::create([
            'case_number' => $caseNumber,
            'case_type' => $request->case_type,
            'complainant_name' => $request->complainant_name,
            'complainant_contact' => $request->complainant_contact,
            'complainant_resident_id' => $request->complainant_resident_id,
            'respondent_name' => $request->respondent_name,
            'respondent_contact' => $request->respondent_contact,
            'respondent_resident_id' => $request->respondent_resident_id,
            'complain_details' => $request->complain_details,
            'incident_date' => $request->incident_date,
            'incident_location' => $request->incident_location,
            'nature_of_complaint' => $request->nature_of_complaint,
            'schedule_date' => $request->case_type === 'summon' ? $request->schedule_date : null,
            'status' => 'pending',
        ]);

        if ($request->case_type === 'summon' && $request->schedule_date) {
            SummonHearing::create([
                'summon_id' => $summon->id,
                'hearing_number' => 1,
                'schedule_date' => $request->schedule_date,
                'remarks' => 'First hearing schedule created.',
                'conducted_by' => 'Punong Barangay'
            ]);
        }

        ActivityLog::log('CREATE_SUMMON', 'Summons', "Created " . ucfirst($request->case_type) . " case {$caseNumber}");

        $caseType = $summon->case_type === 'blotter' ? 'blotter report' : 'summons case';
        $scheduleText = $summon->case_type === 'summon' && $summon->schedule_date
            ? ' Hearing schedule: ' . $summon->schedule_date->format('M d, Y h:i A') . '.'
            : '';
        $smsText = "Barangay Pili: Your {$caseType} {$summon->case_number} has been recorded. Status: Pending.{$scheduleText} Keep this case number and contact the Barangay Hall for assistance.";
        $this->sendCaseSms($summon, $smsText);

        // ── Dispatch Email Notifications ─────────────────────────────────
        $adminEmail = 'admin@brgy-pili.gov.ph';
        
        // 1. Notify Admin
        try {
            \Illuminate\Support\Facades\Mail::send('emails.summon-filed-admin', ['summon' => $summon], function ($message) use ($adminEmail, $summon) {
                $message->to($adminEmail)
                        ->subject('Request for Summon Issuance - Case #' . $summon->case_number);
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to mail Admin for Case {$summon->case_number}: " . $e->getMessage());
        }

        // 2. Notify Complainant (if linked and has email)
        if ($summon->complainant_resident_id) {
            $complainant = $summon->complainantResident;
            if ($complainant && $complainant->email) {
                try {
                    \Illuminate\Support\Facades\Mail::send('emails.summon-acknowledged', ['summon' => $summon], function ($message) use ($complainant, $summon) {
                        $message->to($complainant->email)
                                ->subject('Acknowledgment of Summon Request - Case #' . $summon->case_number);
                    });
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to mail Complainant for Case {$summon->case_number}: " . $e->getMessage());
                }
            }
        }

        // 3. Notify Respondent (if case is summon, respondent is linked, and has email)
        if ($summon->case_type === 'summon' && $summon->respondent_resident_id && $summon->schedule_date) {
            $respondent = $summon->respondentResident;
            if ($respondent && $respondent->email) {
                try {
                    \Illuminate\Support\Facades\Mail::send('emails.summon-notice', ['summon' => $summon], function ($message) use ($respondent, $summon) {
                        $message->to($respondent->email)
                                ->subject('Official Summon Notice - Case #' . $summon->case_number);
                    });
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to mail Respondent for Case {$summon->case_number}: " . $e->getMessage());
                }
            }
        }

        return back()->with('success', "Case {$caseNumber} created successfully.");
    }

    public function update(Request $request)
    {
        $request->validate([
            'summon_id' => 'required|exists:summons,id',
            'status' => 'required|string',
            'hearing_remarks' => 'nullable|string',
            'incident_date' => 'nullable|date',
            'incident_location' => 'nullable|string|max:255',
            'nature_of_complaint' => 'nullable|string|max:255',
            // If scheduling a new hearing
            'new_schedule_date' => 'nullable|date|after_or_equal:today',
            'new_hearing_remarks' => 'nullable|string',
            'new_conducted_by' => 'nullable|string|max:150',
        ]);

        $summon = Summon::with(['complainantResident', 'respondentResident'])->findOrFail($request->summon_id);
        $previousStatus = $summon->status;
        
        $updateData = [
            'status' => $request->status,
            'hearing_remarks' => $request->hearing_remarks,
            'incident_date' => $request->incident_date,
            'incident_location' => $request->incident_location,
            'nature_of_complaint' => $request->nature_of_complaint,
        ];

        $summon->update($updateData);

        // Update latest hearing remarks if exists and no new schedule is added
        if ($request->hearing_remarks && !$request->new_schedule_date) {
            $latestHearing = $summon->hearings()->orderBy('hearing_number', 'desc')->first();
            if ($latestHearing) {
                $latestHearing->update([
                    'remarks' => $request->hearing_remarks
                ]);
            }
        }

        $rescheduled = false;

        // Add new hearing if provided
        if ($request->new_schedule_date) {
            $nextNumber = $summon->hearings()->count() + 1;
            SummonHearing::create([
                'summon_id' => $summon->id,
                'hearing_number' => $nextNumber,
                'schedule_date' => $request->new_schedule_date,
                'remarks' => $request->new_hearing_remarks ?? "Hearing #{$nextNumber} scheduled.",
                'conducted_by' => $request->new_conducted_by ?? 'Punong Barangay'
            ]);

            // Update main record schedule date to the latest
            $summon->update([
                'schedule_date' => $request->new_schedule_date,
                'status' => 'scheduled' // Automatically set status to scheduled on new appointment
            ]);

            $rescheduled = true;
        }

        ActivityLog::log('UPDATE_SUMMON', 'Summons', "Updated case {$summon->case_number}");

        if ($previousStatus !== $summon->status || $rescheduled) {
            $statusLabel = ucwords(str_replace('_', ' ', $summon->status));
            $scheduleText = $rescheduled && $summon->schedule_date
                ? ' New hearing schedule: ' . $summon->schedule_date->format('M d, Y h:i A') . '.'
                : '';
            $smsText = "Barangay Pili case {$summon->case_number} was updated to {$statusLabel}.{$scheduleText} Please check the resident portal or contact the Barangay Hall.";

            $this->sendCaseSms($summon, $smsText);
        }

        // ── Dispatch Rescheduling Email Notifications ─────────────────────
        if ($rescheduled) {
            // 1. Notify Complainant about rescheduled hearing
            if ($summon->complainant_resident_id) {
                $complainant = $summon->complainantResident;
                if ($complainant && $complainant->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::send('emails.summon-acknowledged', ['summon' => $summon], function ($message) use ($complainant, $summon) {
                            $message->to($complainant->email)
                                    ->subject('Rescheduled Summon Hearing - Case #' . $summon->case_number);
                        });
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to mail Complainant on update for Case {$summon->case_number}: " . $e->getMessage());
                    }
                }
            }

            // 2. Notify Respondent about rescheduled hearing
            if ($summon->respondent_resident_id) {
                $respondent = $summon->respondentResident;
                if ($respondent && $respondent->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::send('emails.summon-notice', ['summon' => $summon], function ($message) use ($respondent, $summon) {
                            $message->to($respondent->email)
                                    ->subject('Rescheduled Official Summon Notice - Case #' . $summon->case_number);
                        });
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to mail Respondent on update for Case {$summon->case_number}: " . $e->getMessage());
                    }
                }
            }
        }

        return back()->with('success', "Case {$summon->case_number} updated successfully.");
    }

    public function delete($id)
    {
        $summon = Summon::findOrFail($id);
        $caseNum = $summon->case_number;
        
        // Soft archive instead of hard delete
        $summon->update([
            'archived_at' => now(),
            'archived_by' => Auth::id() ?? 1
        ]);

        ActivityLog::log('DELETE_SUMMON', 'Summons', "Archived case {$caseNum}");

        return back()->with('success', "Case {$caseNum} archived successfully.");
    }

    public function residentIndex()
    {
        $resident = Auth::user()->resident;
        if (!$resident) {
            return redirect()->route('login')->with('error', 'Profile not found.');
        }

        $summons = Summon::with(['complainantResident', 'respondentResident', 'hearings'])
            ->whereNull('archived_at')
            ->where(function ($q) use ($resident) {
                $q->where('complainant_resident_id', $resident->id)
                    ->orWhere('respondent_resident_id', $resident->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(8);

        return view('resident.summons', compact('summons'));
    }

    /**
     * Send a case notification to both parties without texting the same number twice.
     */
    private function sendCaseSms(Summon $summon, string $message): void
    {
        $summon->loadMissing(['complainantResident', 'respondentResident']);

        $contacts = [
            $summon->complainant_contact ?: optional($summon->complainantResident)->contact_number,
            $summon->respondent_contact ?: optional($summon->respondentResident)->contact_number,
        ];

        $sentTo = [];
        foreach (array_filter($contacts) as $contact) {
            $contactKey = preg_replace('/\D/', '', $contact);
            if (isset($sentTo[$contactKey])) {
                continue;
            }

            $sentTo[$contactKey] = true;
            SmsService::send($contact, $message);
        }
    }
}
