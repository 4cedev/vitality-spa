<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        // Allowed while a password change is pending: the password page itself,
        // logging out, and the requests that save the new password.
        if ($request->routeIs('security.edit', 'logout', 'password.*', 'user-password.*', 'livewire.*')
            || $request->headers->has('X-Livewire')) {
            return $next($request);
        }

        return redirect()->route('security.edit')->with(
            'password_notice',
            'Please set a new password before continuing.'
        );
    }
}