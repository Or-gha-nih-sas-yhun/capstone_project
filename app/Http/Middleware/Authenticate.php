<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
<<<<<<< HEAD
            $adminDomain = config('app.admin_domain', env('ADMIN_DOMAIN', 'admin.brgypilieclearance.com'));
=======
            $adminDomain = config('barangay.admin_domain');
>>>>>>> origin/master
            if ($request->is('admin*') || $request->getHost() === $adminDomain) {
                return route('admin.login');
            }
            return route('login');
        }
    }
}
