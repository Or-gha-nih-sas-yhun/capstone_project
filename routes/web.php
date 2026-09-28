<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ResidentManagementController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SummonController;
use App\Http\Controllers\BulletinController;
use App\Http\Controllers\BorrowRequestController;

$adminDomain = config('app.admin_domain', env('ADMIN_DOMAIN', 'admin.brgypilieclearance.com'));

// ── Admin Subdomain Routes (admin.brgypilieclearance.com) ──
Route::domain($adminDomain)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/', [AuthController::class, 'showAdminLogin'])->name('admin.subdomain.root');
        Route::get('/login', [AuthController::class, 'showAdminLogin'])->name('admin.subdomain.login');
        Route::post('/login', [AuthController::class, 'adminLogin'])->name('admin.subdomain.login.submit');
        Route::get('/verify-otp', [AuthController::class, 'showAdminOtp'])->name('admin.subdomain.otp.notice');
        Route::post('/verify-otp', [AuthController::class, 'verifyAdminOtp'])->name('admin.subdomain.otp.verify');
        Route::post('/resend-otp', [AuthController::class, 'resendAdminOtp'])->name('admin.subdomain.otp.resend');
        Route::get('/cancel-otp', [AuthController::class, 'cancelAdminOtp'])->name('admin.subdomain.otp.cancel');
    });

    Route::middleware(['auth', 'role:staff,admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.subdomain.dashboard');
    });
});

// ── Admin Authentication Routes (Path-based & Local Dev) ──
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showAdminLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'adminLogin'])->name('login.submit');
        Route::get('/verify-otp', [AuthController::class, 'showAdminOtp'])->name('otp.notice');
        Route::post('/verify-otp', [AuthController::class, 'verifyAdminOtp'])->name('otp.verify');
        Route::post('/resend-otp', [AuthController::class, 'resendAdminOtp'])->name('otp.resend');
        Route::get('/cancel-otp', [AuthController::class, 'cancelAdminOtp'])->name('otp.cancel');
    });
});

// ── Public Routes (Resident & General) ────────────────────
Route::get('/', function () {
    if (str_contains(request()->header('User-Agent'), 'BrgyPiliApp')) {
        return redirect()->route('login');
    }
    $bulletins = \App\Models\BulletinAnnouncement::orderBy('is_pinned', 'desc')
        ->orderBy('published_at', 'desc')
        ->limit(3)
        ->get();
    return view('welcome', compact('bulletins'));
})->name('index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // GET /register redirects to /login and opens the register tab
    Route::get('/register', function () {
        session()->flash('reg_tab', true);
        return redirect()->route('login');
    })->name('register');
    
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // Email Verification
    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/verify-email/resend', [AuthController::class, 'resendVerification'])->name('verification.resend');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::match(['get', 'post'], '/track', [TrackingController::class, 'track'])->name('track');
Route::get('/mobile-app', function () {
    return view('mobile_app');
})->name('mobile.app');

Route::middleware('auth')->get('/home', function () {
    $role = auth()->user()->role;
    if ($role === 'resident') {
        return redirect()->route('resident.my_requests');
    }
    return redirect()->route('admin.dashboard');
})->name('home');

// ── Resident Routes ───────────────────────────────────────
Route::middleware(['auth', 'role:resident'])->prefix('resident')->name('resident.')->group(function () {
    Route::get('/dashboard', [ResidentController::class, 'home'])->name('dashboard');

    Route::get('/my-requests', [ResidentController::class, 'myRequests'])->name('my_requests');

    Route::get('/request', [ResidentController::class, 'newRequestForm'])->name('request');
    Route::post('/request', [ResidentController::class, 'submitRequest']);

    Route::get('/profile', [ResidentController::class, 'profileForm'])->name('profile');
    Route::post('/profile', [ResidentController::class, 'updateProfile']);

    // Summons
    Route::get('/summons', [SummonController::class, 'residentIndex'])->name('summons');

    // Bulletins/Announcements
    Route::get('/bulletins', [BulletinController::class, 'residentIndex'])->name('bulletins');

    // Borrow Tents/Chairs
    Route::get('/borrows', [BorrowRequestController::class, 'residentIndex'])->name('borrows');
    Route::post('/borrows/store', [BorrowRequestController::class, 'store'])->name('borrows.store');
});

// ── Admin & Staff Routes ──────────────────────────────────
Route::middleware(['auth', 'role:staff,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [AdminController::class, 'profileForm'])->name('profile');
    Route::post('/profile', [AdminController::class, 'updateProfile']);

    // Requests Management
    Route::get('/requests', [RequestController::class, 'index'])->name('requests');
    Route::post('/requests/update-status', [RequestController::class, 'updateStatus'])->name('requests.update_status');
    Route::post('/requests/payment', [RequestController::class, 'processPayment'])->name('requests.payment');

    // Payments Monitoring
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
    Route::post('/payments/update', [PaymentController::class, 'update'])->name('payments.update');

    // Summons Management
    Route::get('/summons', [SummonController::class, 'index'])->name('summons');
    Route::post('/summons/store', [SummonController::class, 'store'])->name('summons.store');
    Route::post('/summons/update', [SummonController::class, 'update'])->name('summons.update');
    Route::get('/summons/delete/{id}', [SummonController::class, 'delete'])->name('summons.delete');

    // Bulletins Management
    Route::get('/bulletins', [BulletinController::class, 'index'])->name('bulletins');
    Route::post('/bulletins/store', [BulletinController::class, 'store'])->name('bulletins.store');
    Route::post('/bulletins/update', [BulletinController::class, 'update'])->name('bulletins.update');
    Route::get('/bulletins/delete/{id}', [BulletinController::class, 'delete'])->name('bulletins.delete');

    // Borrow Equipment Management
    Route::get('/borrows', [BorrowRequestController::class, 'index'])->name('borrows');
    Route::post('/borrows/update-status', [BorrowRequestController::class, 'updateStatus'])->name('borrows.update_status');

    // Residents Management
    Route::get('/residents', [ResidentManagementController::class, 'index'])->name('residents');
    Route::post('/residents/store', [ResidentManagementController::class, 'store'])->name('residents.store');
    Route::get('/residents/approve/{id}', [ResidentManagementController::class, 'approve'])->name('residents.approve');
    Route::get('/residents/reject/{id}', [ResidentManagementController::class, 'reject'])->name('residents.reject');
    Route::get('/residents/delete/{id}', [ResidentManagementController::class, 'delete'])->name('residents.delete');

    // Certificate Types Management
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates');
    Route::post('/certificates/store', [CertificateController::class, 'store'])->name('certificates.store');
    Route::get('/certificates/delete/{id}', [CertificateController::class, 'delete'])->name('certificates.delete');

    // Officials Management
    Route::get('/officials', [OfficialController::class, 'index'])->name('officials');
    Route::post('/officials/store', [OfficialController::class, 'store'])->name('officials.store');
    Route::get('/officials/archive/{id}', [OfficialController::class, 'archive'])->name('officials.archive');
    Route::get('/officials/delete/{id}', [OfficialController::class, 'delete'])->name('officials.delete');

    // Admin Only sections
    Route::middleware('role:admin')->group(function () {
        // User accounts management
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/delete/{id}', [UserController::class, 'delete'])->name('users.delete');

        // Archive management
        Route::get('/archive', [ArchiveController::class, 'index'])->name('archive');
        Route::get('/archive/restore/{type}/{id}', [ArchiveController::class, 'restore'])->name('archive.restore');

        // Reports and Activity logs
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity_logs');
    });
});

// ── Print Routes ──────────────────────────────────────────
Route::middleware('auth')->get('/print/certificate/{id}', [PrintController::class, 'print'])->name('print.certificate');
Route::middleware('auth')->get('/print/summon/{id}/{form_type}', [PrintController::class, 'printSummon'])->name('print.summon');

// ── Temporary Secure Migration Trigger for Vercel ──────────────────
Route::get('/run-migrations', function (\Illuminate\Http\Request $request) {
    if ($request->query('key') !== 'pili2026') {
        abort(403, 'Unauthorized');
    }
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return 'Migrations completed successfully!<br><pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return 'Error running migrations: ' . $e->getMessage();
    }
});
