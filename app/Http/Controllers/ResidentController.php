<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Certificate;
use App\Models\Request as CertificateRequest;
use App\Models\Payment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResidentController extends Controller
{
    public function home()
    {
        $resident = Auth::user()->resident;
        if (!$resident) {
            return redirect()->route('login')->with('error', 'Profile not found.');
        }

        $recentRequests = CertificateRequest::with('certificate')
            ->where('resident_id', $resident->id)
            ->whereNull('archived_at')
            ->latest('requested_at')
            ->take(3)
            ->get();

        return view('resident.home', compact('resident', 'recentRequests'));
    }

    public function myRequests()
    {
        $resident = Auth::user()->resident;
        if (!$resident) {
            return redirect()->route('login')->with('error', 'Profile not found.');
        }

        $requests = CertificateRequest::with(['certificate', 'payment'])
            ->where('resident_id', $resident->id)
            ->whereNull('archived_at')
            ->orderBy('requested_at', 'desc')
            ->paginate(8);

        return view('resident.my_requests', compact('requests'));
    }

    public function newRequestForm()
    {
        $resident = Auth::user()->resident;
        $certs = Certificate::where('status', 'active')->orderBy('name')->get();
        return view('resident.request', compact('resident', 'certs'));
    }

    public function submitRequest(Request $request)
    {
        $request->validate([
            'certificate_id' => 'required|exists:certificates,id',
            'purpose' => 'required|string|max:500',
        ]);

        $resident = Auth::user()->resident;
        if (!$resident) {
            return redirect()->route('login')->with('error', 'Profile not found.');
        }

        $cert = Certificate::findOrFail($request->certificate_id);

        // Generate unique tracking number
        $trackingNumber = 'PILI-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Create Request
        $certReq = CertificateRequest::create([
            'tracking_number' => $trackingNumber,
            'resident_id' => $resident->id,
            'certificate_id' => $cert->id,
            'purpose' => $request->purpose,
            'status' => 'pending',
        ]);

        // Create Payment
        Payment::create([
            'request_id' => $certReq->id,
            'amount' => $cert->fee,
            'payment_method' => 'cash',
            'payment_status' => ($cert->fee == 0) ? 'waived' : 'unpaid',
        ]);

        ActivityLog::log('NEW_REQUEST', 'Requests', "Filed request $trackingNumber");

        return redirect()->route('resident.my_requests')
            ->with('success', "Request submitted! Tracking number: $trackingNumber. Please proceed to Barangay Pili Office for payment.");
    }

    public function profileForm()
    {
        $resident = Auth::user()->resident;
        return view('resident.profile', compact('resident'));
    }

    public function updateProfile(Request $request)
    {
        $resident = Auth::user()->resident;
        if (!$resident) {
            return redirect()->route('login')->with('error', 'Profile not found.');
        }

        $action = $request->input('action');

        if ($action === 'update_profile') {
            $request->validate([
                'first_name' => 'required|string|regex:/^[a-zA-Z\s\-\.,]+$/',
                'last_name' => 'required|string|regex:/^[a-zA-Z\s\-\.,]+$/',
                'middle_name' => 'nullable|string|regex:/^[a-zA-Z\s\-\.,]+$/',
                'contact_number' => 'nullable|string|max:20',
                'address' => 'required|string|max:255',
                'purok' => 'nullable|string|max:100',
                'occupation' => 'nullable|string|max:150',
                'civil_status' => 'required|in:Single,Married,Widowed,Separated',
                'voter_status' => 'required|in:Registered,Not Registered',
            ], [
                'first_name.regex' => 'First name must contain only letters, spaces, hyphens, and periods.',
                'last_name.regex' => 'Last name must contain only letters, spaces, hyphens, and periods.',
                'middle_name.regex' => 'Middle name must contain only letters, spaces, hyphens, and periods.',
            ]);

            $resident->update($request->only([
                'first_name', 'middle_name', 'last_name', 'contact_number',
                'address', 'purok', 'occupation', 'civil_status', 'voter_status'
            ]));

            ActivityLog::log('UPDATE_PROFILE', 'Profile', 'Resident updated profile info');
            return back()->with('success', 'Profile updated successfully.');

        } elseif ($action === 'change_password') {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6',
                'confirm_password' => 'required|string|same:new_password',
            ]);

            $user = Auth::user();

            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return back()->with('error', 'Current password is incorrect.');
            }

            // Generate 6-digit OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));

            // Save to session
            $request->session()->put('password_change_otp', $otp);
            $request->session()->put('password_change_new', \Illuminate\Support\Facades\Hash::make($request->new_password));

            // Send Email OTP
            $email = $user->email;
            try {
                \Illuminate\Support\Facades\Mail::send('emails.password-otp', ['code' => $otp], function ($message) use ($email) {
                    $fromAddress = config('mail.from.address') ?: 'no-reply@brgypilieclearance.com';
                    $fromName = config('mail.from.name') ?: 'Barangay Pili Clearance';
                    $message->from($fromAddress, $fromName);
                    $message->to($email);
                    $message->subject('Confirm Password Change - Barangay Pili Clearance & Certificate System');
                });
                \Illuminate\Support\Facades\Log::info("Password change OTP sent to {$email}: {$otp}");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send password OTP to {$email}: " . $e->getMessage());
            }

            return back()->with('success', 'A 6-digit verification code has been sent to your email. Please enter it below to confirm and finalize your password change.')
                ->with('password_change_pending', true);

        } elseif ($action === 'verify_password_otp') {
            $request->validate([
                'otp_code' => 'required|string|size:6',
            ]);

            if (!$request->session()->has('password_change_otp') || !$request->session()->has('password_change_new')) {
                return back()->with('error', 'Your password change session has expired. Please request a new password change.');
            }

            if ($request->otp_code !== $request->session()->get('password_change_otp')) {
                return back()->with('error', 'Invalid verification code. Please check and try again.')
                    ->with('password_change_pending', true);
            }

            $user = Auth::user();
            $user->update([
                'password' => $request->session()->get('password_change_new')
            ]);

            // Clear session values
            $request->session()->forget(['password_change_otp', 'password_change_new']);

            ActivityLog::log('CHANGE_PASSWORD', 'Profile', 'Password changed with email OTP confirmation');
            return back()->with('success', 'Password changed successfully!');

        } elseif ($action === 'update_photo') {
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/uploads/profiles'), $filename);

                // Delete old photo
                if ($resident->photo && file_exists(public_path('assets/uploads/' . $resident->photo))) {
                    @unlink(public_path('assets/uploads/' . $resident->photo));
                }

                $resident->update([
                    'photo' => 'profiles/' . $filename
                ]);

                ActivityLog::log('UPDATE_PROFILE_PHOTO', 'Profile', 'Resident updated profile photo');
                return back()->with('success', 'Profile photo updated.');
            }

            return back()->with('error', 'Unable to upload photo.');

        } elseif ($action === 'remove_photo') {
            if ($resident->photo && file_exists(public_path('assets/uploads/' . $resident->photo))) {
                @unlink(public_path('assets/uploads/' . $resident->photo));
            }

            $resident->update([
                'photo' => null
            ]);

            ActivityLog::log('REMOVE_PROFILE_PHOTO', 'Profile', 'Resident removed profile photo');
            return back()->with('success', 'Profile photo removed.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
