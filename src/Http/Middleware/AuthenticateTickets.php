<?php

namespace Tishmalo\EscalationMatrix\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateTickets
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if authentication is enabled
        if (!config('escalation.tickets_auth.enabled', true)) {
            return $next($request);
        }

        // Strategy 1: Check Laravel Auth (if user is logged in)
        if (Auth::check()) {
            if ($this->isAuthorizedUser(Auth::user())) {
                return $next($request);
            }

            // User is logged in but not authorized
            abort(403, 'You do not have permission to access escalation tickets.');
        }

        // Strategy 2: Check session for password authentication
        if (session('escalation_tickets_authenticated')) {
            return $next($request);
        }

        // Not authenticated - redirect to login
        return redirect()->route('escalation.login');
    }

    /**
     * Check if authenticated user is authorized to view tickets
     *
     * @param  mixed  $user
     * @return bool
     */
    protected function isAuthorizedUser($user): bool
    {
        // Check allowed emails
        $allowedEmails = config('escalation.tickets_auth.allowed_emails', []);
        if (!empty($allowedEmails) && in_array($user->email, $allowedEmails)) {
            return true;
        }

        // Check allowed roles
        $allowedRoles = config('escalation.tickets_auth.allowed_roles', []);
        if (!empty($allowedRoles)) {
            // Try Spatie Permission package
            if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($allowedRoles)) {
                return true;
            }

            // Try Laravel's built-in role (if user has 'role' attribute)
            if (isset($user->role) && in_array($user->role, $allowedRoles)) {
                return true;
            }

            // Try Laravel's built-in roles relationship
            if (method_exists($user, 'roles')) {
                $userRoles = $user->roles->pluck('name')->toArray();
                if (!empty(array_intersect($allowedRoles, $userRoles))) {
                    return true;
                }
            }
        }

        return false;
    }
}
