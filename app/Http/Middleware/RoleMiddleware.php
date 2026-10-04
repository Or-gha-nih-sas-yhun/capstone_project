<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $adminDomain = config('app.admin_domain', env('ADMIN_DOMAIN', 'admin.brgypilieclearance.com'));
        $adminLoginRoute = ($request->is('admin*') || $request->getHost() === $adminDomain) ? 'admin.login' : 'login';

        if (!Auth::check()) {
            return redirect()->route($adminLoginRoute);
        }

        $user = Auth::user();

        // Admin has super-admin rights and bypasses checks
        if ($user->role === 'admin') {
            return $next($request);
        }

        // If the user's status is not active, force logout
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route($adminLoginRoute)->with('error', 'Your account status is: ' . $user->status . '.');
        }

        // Check if user has any of the required roles
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        return redirect()->route($adminLoginRoute)->with('error', 'Unauthorized access.');
    }
}
