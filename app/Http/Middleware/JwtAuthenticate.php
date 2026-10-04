<?php

namespace App\Http\Middleware;

use App\Helpers\JwtHelper;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * JWT Authentication middleware for stateless API routes.
 *
 * Expects: Authorization: Bearer <token>
 *
 * On success: authenticates the user for this request via Auth::loginUsingId()
 * On failure: returns 401 JSON response
 */
class JwtAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token missing. Include Authorization: Bearer <token> header.',
            ], 401);
        }

        $token = substr($authHeader, 7);

        try {
            $payload = JwtHelper::verify($token);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }

        $userId = $payload['sub'] ?? null;
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token payload.',
            ], 401);
        }

        $user = User::find($userId);
        if (!$user || $user->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'User account not found or suspended.',
            ], 401);
        }

        // Authenticate for the duration of this request
        Auth::loginUsingId($userId);

        return $next($request);
    }
}
