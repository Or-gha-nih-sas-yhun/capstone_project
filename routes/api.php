<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ── Public API Endpoints ───────────────────────────────────────────────
// POST /api/login — Authenticate and receive a JWT token
Route::post('/login', [AuthController::class, 'apiLogin'])->name('api.login');

// ── JWT-Protected API Endpoints ────────────────────────────────────────
Route::middleware('jwt.auth')->group(function () {
    // GET /api/me — Return the currently authenticated user's info
    Route::get('/me', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user' => [
                'id'         => $user->id,
                'username'   => $user->username,
                'email'      => $user->email,
                'role'       => $user->role,
                'status'     => $user->status,
            ],
        ]);
    })->name('api.me');

    // POST /api/logout — Stateless logout (client discards token)
    Route::post('/logout', function (Request $request) {
        \App\Models\ActivityLog::log('API_LOGOUT', 'Auth', 'User logged out via API');
        return response()->json([
            'success' => true,
            'message' => 'Logged out. Please discard your token on the client side.',
        ]);
    })->name('api.logout');
});
