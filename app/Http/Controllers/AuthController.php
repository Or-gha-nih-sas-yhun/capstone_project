<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Resident;
use App\Models\ActivityLog;
use App\Helpers\JwtHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // ── Standard (Resident) Login ─────────────────────────────
    public function showLogin()
    {
        $adminDomain = config('app.admin_domain', env('ADMIN_DOMAIN', 'admin.brgypilieclearance.com'));
        if (request()->getHost() === $adminDomain) {
            return $this->showAdminLogin();
        }

        if (Auth::check()) {
            return $this->redirectUser();
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email|string',
            'password' => 'required|string',
        ]);

        // Google reCAPTCHA verification (for web logins, exempts Android APK)
        $isMobileApp = str_contains((string) $request->userAgent(), 'BrgyPiliApp');
        if (!$isMobileApp && !$this->verifyRecaptcha($request)) {
            return back()->with('error', 'Please complete the Google reCAPTCHA security verification to proceed.')
                ->withInput($request->only('email'));
        }

        $user = User::where('email', $credentials['email'])->first();

        // If credentials mismatch or account is not a resident (e.g. admin/staff trying resident portal)
        if (!$user || !Hash::check($credentials['password'], $user->password) || $user->role !== 'resident') {
            return back()->with('error', 'User not found or invalid credentials. Please try again.')
                ->withInput($request->only('email'));
        }

        // Email verification check
        if ($user->email_verified_at === null) {
            if (!$user->verification_code) {
                $user->verification_code = sprintf("%06d", mt_rand(100000, 999999));
                $user->save();
                $this->sendVerificationEmail($user);
            }
            $request->session()->put('verify_email', $user->email);
            return redirect()->route('verification.notice')->with('error', 'Please verify your email address first.');
        }

        if ($user->status === 'inactive') {
            return back()->with('error', 'Your account is pending approval by the administrator.');
        }
        if ($user->status === 'suspended') {
            return back()->with('error', 'Your account has been suspended.');
        }

        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();

        ActivityLog::log('LOGIN', 'Auth', 'User logged in');

        return $this->redirectUser();
    }

    // ── Dedicated Admin Auth (admin.brgypilieclearance.com or /admin/login) ────
    public function showAdminLogin()
    {
        if (Auth::check()) {
            if (in_array(Auth::user()->role, ['admin', 'staff'])) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('resident.my_requests');
        }

        return view('auth.admin-login');
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email|string',
            'password' => 'required|string',
        ]);

        // 1. Mandatory Geolocation Verification for Admin Security
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        if (empty($lat) || empty($lng) || !is_numeric($lat) || !is_numeric($lng) ||
            $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return back()->with('error', 'Location access is strictly required to log in to the administrative portal. Please enable location permissions in your browser and try again.')
                ->withInput($request->only('email'));
        }

        $location = "{$lat},{$lng}";

        // 2. Google reCAPTCHA Verification
        if (!$this->verifyRecaptcha($request)) {
            return back()->with('error', 'Please complete the Google reCAPTCHA security verification to proceed.')
                ->withInput($request->only('email'));
        }

        // 3. Validate User Credentials
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->with('error', 'Invalid administrative email address or password. Please try again.')
                ->withInput($request->only('email'));
        }

        // 4. Ensure role is admin or staff (restrict residents)
        if (!in_array($user->role, ['admin', 'staff'])) {
            return back()->with('error', 'Access restricted: This portal is reserved for Barangay Officials and Staff only. Residents please sign in via the resident portal.')
                ->withInput($request->only('email'));
        }

        // 5. Status Checks
        if ($user->status === 'inactive') {
            return back()->with('error', 'Your administrative account is inactive. Please contact the lead administrator.');
        }
        if ($user->status === 'suspended') {
            return back()->with('error', 'Your account has been suspended. Please contact the administrator.');
        }

        // 6. Generate Secure 6-digit Email OTP (Valid for 10 minutes)
        $otp = sprintf("%06d", random_int(100000, 999999));
        $user->admin_otp_code = $otp;
        $user->admin_otp_expires_at = now()->addMinutes(10);
        $user->save();

        // 7. Set Pending 2FA Session
        $request->session()->put('admin_otp_user_id', $user->id);
        $request->session()->put('admin_remember', $request->boolean('remember'));
        $request->session()->put('admin_otp_last_sent', now()->timestamp);
        $request->session()->put('admin_otp_location', $location);

        // 8. Dispatch OTP Email
        $this->sendAdminOtpEmail($user, $otp);

        ActivityLog::log('ADMIN_OTP_SENT', 'Auth', "Admin login OTP dispatched to {$user->email}", $location);

        return redirect()->route('admin.otp.notice')
            ->with('success', 'A 6-digit verification code has been dispatched to your registered email address.');
    }

    public function showAdminOtp(Request $request)
    {
        $userId = $request->session()->get('admin_otp_user_id');
        if (!$userId) {
            return redirect()->route('admin.login')->with('error', 'Your verification session expired. Please sign in again.');
        }

        $user = User::find($userId);
        if (!$user) {
            $request->session()->forget(['admin_otp_user_id', 'admin_remember', 'admin_otp_last_sent']);
            return redirect()->route('admin.login');
        }

        // Mask email: e.g., d***a@example.com
        $parts = explode('@', $user->email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        $maskedName = strlen($name) <= 2 ? $name . '***' : substr($name, 0, 1) . '***' . substr($name, -1);
        $maskedEmail = $maskedName . '@' . $domain;

        $expiresTimestamp = $user->admin_otp_expires_at ? $user->admin_otp_expires_at->timestamp : (time() + 600);
        $resendCooldownEnd = $request->session()->get('admin_otp_last_sent', time()) + 60;

        return view('auth.admin-verify-otp', compact('maskedEmail', 'expiresTimestamp', 'resendCooldownEnd'));
    }

    public function verifyAdminOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = $request->session()->get('admin_otp_user_id');
        if (!$userId) {
            return redirect()->route('admin.login')->with('error', 'Your verification session expired. Please sign in again.');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Check expiration
        if (!$user->admin_otp_expires_at || now()->gt($user->admin_otp_expires_at)) {
            return back()->with('error', 'The verification code has expired. Please request a new code.');
        }

        // Constant-time comparison
        if (!hash_equals((string) $user->admin_otp_code, (string) $request->code)) {
            return back()->with('error', 'Invalid verification code. Please check your email and try again.');
        }

        // Clear OTP fields
        $user->admin_otp_code = null;
        $user->admin_otp_expires_at = null;
        $user->save();

        $remember = $request->session()->get('admin_remember', false);
        $location = $request->session()->get('admin_otp_location');
        $request->session()->forget(['admin_otp_user_id', 'admin_remember', 'admin_otp_last_sent', 'admin_otp_location']);

        // Authenticate the user
        Auth::login($user, $remember);
        $request->session()->regenerate();

        ActivityLog::log('ADMIN_LOGIN_SUCCESS', 'Auth', "User {$user->username} successfully authenticated via 2FA Email OTP", $location);

        return redirect()->route('admin.dashboard')->with('success', "Welcome back, {$user->username}!");
    }

    public function resendAdminOtp(Request $request)
    {
        $userId = $request->session()->get('admin_otp_user_id');
        if (!$userId) {
            return redirect()->route('admin.login')->with('error', 'Your verification session expired. Please sign in again.');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Cooldown enforcement (60 seconds)
        $lastSent = $request->session()->get('admin_otp_last_sent', 0);
        if (time() - $lastSent < 60) {
            $remaining = 60 - (time() - $lastSent);
            return back()->with('error', "Please wait {$remaining} seconds before requesting a new code.");
        }

        $otp = sprintf("%06d", random_int(100000, 999999));
        $user->admin_otp_code = $otp;
        $user->admin_otp_expires_at = now()->addMinutes(10);
        $user->save();

        $request->session()->put('admin_otp_last_sent', now()->timestamp);

        $this->sendAdminOtpEmail($user, $otp);

        ActivityLog::log('ADMIN_OTP_RESENT', 'Auth', "Fresh OTP code dispatched to {$user->email}");

        return back()->with('success', 'A fresh 6-digit verification code has been dispatched to your email.');
    }

    public function cancelAdminOtp(Request $request)
    {
        $request->session()->forget(['admin_otp_user_id', 'admin_remember', 'admin_otp_last_sent']);
        return redirect()->route('admin.login');
    }

    // ── Helper: Verify Google reCAPTCHA ──────────────────────
    private function verifyRecaptcha(Request $request): bool
    {
        $secretKey = config('services.recaptcha.secret_key');
        $recaptchaResponse = $request->input('g-recaptcha-response');

        // If no secret key configured or running in local test without key, allow fallback
        if (empty($secretKey)) {
            return true;
        }

        if (empty($recaptchaResponse)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip(),
            ]);

            return (bool) $response->json('success');
        } catch (\Exception $e) {
            Log::error("reCAPTCHA validation request failed: " . $e->getMessage());
            // In local development, don't block if there is a network glitch with Google servers
            if (app()->environment('local')) {
                return true;
            }
            return false;
        }
    }

    // ── API Login — Issues JWT for Mobile App ─────────────────
    public function apiLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        if ($user->role !== 'resident') {
            return response()->json([
                'success' => false,
                'message' => 'This API endpoint is for resident accounts only.',
            ], 403);
        }

        if ($user->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is pending approval by the administrator.',
            ], 403);
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended.',
            ], 403);
        }

        if ($user->email_verified_at === null) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before logging in.',
            ], 403);
        }

        $token = JwtHelper::generate($user->id, $user->role);

        ActivityLog::log('API_LOGIN', 'Auth', "Resident {$user->username} authenticated via API (JWT)");

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
            ],
        ]);
    }

    // ── Helper: Send Admin OTP Email ──────────────────────────
    private function sendAdminOtpEmail($user, $otp)
    {
        $email = $user->email;
        try {
            Mail::send('emails.admin-login-otp', ['code' => $otp, 'user' => $user], function ($message) use ($email) {
                $fromAddress = config('mail.from.address') ?: 'no-reply@brgypilieclearance.com';
                $fromName = config('mail.from.name') ?: 'Barangay Pili Clearance';
                $message->from($fromAddress, $fromName);
                $message->to($email);
                $message->subject('Admin Portal Verification Code - Barangay Pili');
            });
            Log::info("Admin 2FA OTP sent to {$email}: {$otp}");
        } catch (\Exception $e) {
            Log::error("Failed to send admin OTP email to {$email}: " . $e->getMessage());
            // Also log OTP code for local debugging fallback
            Log::info("Admin 2FA OTP (local fallback log) for {$email}: {$otp}");
        }
    }

    // ── Resident Registration ─────────────────────────────────
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|regex:/^[a-zA-Z\s\-\.,]+$/',
            'last_name' => 'required|string|regex:/^[a-zA-Z\s\-\.,]+$/',
            'middle_name' => 'nullable|string|regex:/^[a-zA-Z\s\-\.,]+$/',
            'gender' => 'required|in:Male,Female,Other',
            'birthdate' => 'required|date|before_or_equal:today',
            'civil_status' => 'required|in:Single,Married,Widowed,Separated',
            'contact_number' => 'nullable|string',
            'purok' => 'nullable|string',
            'years_of_residency' => 'nullable|integer|min:0',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'first_name.regex' => 'First name must contain only letters, spaces, hyphens, and periods.',
            'last_name.regex' => 'Last name must contain only letters, spaces, hyphens, and periods.',
            'middle_name.regex' => 'Middle name must contain only letters, spaces, hyphens, and periods.',
        ]);

        try {
            DB::beginTransaction();

            // 1. Create Resident profile
            $resident = Resident::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'birthdate' => $request->birthdate,
                'civil_status' => $request->civil_status,
                'contact_number' => $request->contact_number,
                'email' => $request->email,
                'address' => 'Barangay Pili, Madridejos, Cebu', // Default fallback address
                'purok' => $request->purok,
                'voter_status' => 'Not Registered',
                'years_of_residency' => $request->years_of_residency ?? 0,
                'status' => 'active'
            ]);

            $code = sprintf("%06d", mt_rand(100000, 999999));

            // 2. Create User account linked to Resident
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'resident',
                'status' => 'inactive', // pending approval
                'resident_id' => $resident->id,
                'verification_code' => $code,
                'email_verified_at' => null,
            ]);

            $this->sendVerificationEmail($user);

            DB::commit();

            $request->session()->put('verify_email', $user->email);

            return redirect()->route('verification.notice')->with('success', 'Registration Step 2 complete! Please verify your email address (Step 3) to complete registration.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Registration failed exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            $msg = 'Registration failed. Please try again.';
            if (config('app.debug')) {
                $msg .= ' Details: ' . $e->getMessage();
            }
            return back()->with('error', $msg)->withInput();
        }
    }

    public function logout(Request $request)
    {
        $wasAdmin = Auth::check() && in_array(Auth::user()->role, ['admin', 'staff']);
        $adminDomain = config('app.admin_domain', env('ADMIN_DOMAIN', 'admin.brgypilieclearance.com'));
        $isOnAdminDomain = request()->getHost() === $adminDomain;

        if (Auth::check()) {
            ActivityLog::log('LOGOUT', 'Auth', 'User logged out');
        }

        Auth::guard('web')->logout();

        $request->session()->flush();
        $request->session()->regenerate(true);

        $cookie = \Cookie::forget(config('session.cookie', 'laravel_session'));

        if ($wasAdmin || $isOnAdminDomain) {
            return redirect()->route('admin.login')->withCookie($cookie);
        }

        return redirect()->route('login')->withCookie($cookie);
    }

    private function redirectUser()
    {
        $role = Auth::user()->role;
        if ($role === 'resident') {
            return redirect()->route('resident.my_requests');
        }
        return redirect()->route('admin.dashboard');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        $token = \Illuminate\Support\Str::random(60);
        $hashedToken = hash('sha256', $token);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $hashedToken,
                'created_at' => now()
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token]) . '?email=' . urlencode($request->email);

        $email = $request->email;
        try {
            Mail::send('emails.forgot-password', ['resetUrl' => $resetUrl], function ($message) use ($email) {
                $fromAddress = config('mail.from.address') ?: 'no-reply@brgypilieclearance.com';
                $fromName = config('mail.from.name') ?: 'Barangay Pili Clearance';
                $message->from($fromAddress, $fromName);
                $message->to($email);
                $message->subject('Reset Password - Barangay Pili Clearance & Certificate System');
            });

            Log::info("Password reset link requested for {$email}: {$resetUrl}");

            return back()->with('success', 'Successfully sent the reset link');
        } catch (\Exception $e) {
            Log::error("Failed to send password reset email to {$email}: " . $e->getMessage());
            Log::info("Password reset link (fallback) for {$email}: {$resetUrl}");

            return back()->with('success', 'Successfully sent the reset link');
        }
    }

    public function showResetPassword($token, Request $request)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email')
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$record || !hash_equals($record->token, hash('sha256', $request->token))) {
            return back()->withErrors(['email' => 'This password reset token is invalid.']);
        }

        $expiresAt = \Carbon\Carbon::parse($record->created_at)->addMinutes(60);
        if ($expiresAt->isPast()) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'This password reset token has expired.']);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        ActivityLog::log('PASSWORD_RESET', 'Auth', "User {$user->username} reset their password");

        return redirect()->route('login')->with('success', 'Your password has been successfully reset! You can now log in.');
    }

    public function showVerifyEmail(Request $request)
    {
        $email = $request->session()->get('verify_email') ?: $request->query('email');
        if (!$email) {
            return redirect()->route('login')->with('error', 'Invalid verification request.');
        }

        return view('auth.verify-email', compact('email'));
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at !== null) {
            return redirect()->route('login')->with('success', 'Your email is already verified. Please wait for admin approval.');
        }

        if ($user->verification_code !== $request->code) {
            return back()->with('error', 'Invalid verification code. Please check and try again.');
        }

        // Complete Verification
        $user->verification_code = null;
        $user->email_verified_at = now();
        $user->save();

        ActivityLog::log('EMAIL_VERIFIED', 'Auth', "User {$user->username} verified their email address");

        // Forget verify_email from session
        $request->session()->forget('verify_email');

        return redirect()->route('login')->with('success', 'Email verification successful! Your account is now pending approval by the administrator.');
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at !== null) {
            return redirect()->route('login')->with('success', 'Your email is already verified.');
        }

        // Regenerate verification code
        $user->verification_code = sprintf("%06d", mt_rand(100000, 999999));
        $user->save();

        $this->sendVerificationEmail($user);

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    private function sendVerificationEmail($user)
    {
        $code = $user->verification_code;
        $email = $user->email;
        try {
            Mail::send('emails.verify-email', ['code' => $code], function ($message) use ($email) {
                $fromAddress = config('mail.from.address') ?: 'no-reply@brgypilieclearance.com';
                $fromName = config('mail.from.name') ?: 'Barangay Pili Clearance';
                $message->from($fromAddress, $fromName);
                $message->to($email);
                $message->subject('Verify Your Email Address - Barangay Pili Clearance & Certificate System');
            });
            Log::info("Verification code sent to {$email}: {$code}");
        } catch (\Exception $e) {
            Log::error("Failed to send verification email to {$email}: " . $e->getMessage());
        }
    }
}
