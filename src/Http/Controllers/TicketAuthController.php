<?php

namespace Tishmalo\EscalationMatrix\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class TicketAuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // If user is already authenticated via Laravel auth and authorized, redirect to tickets
        if (auth()->check()) {
            return redirect()->route('escalation.tickets.index');
        }

        // If already authenticated via password, redirect to tickets
        if (session('escalation_tickets_authenticated')) {
            return redirect()->route('escalation.tickets.index');
        }

        return view('escalation-matrix::auth.login');
    }

    /**
     * Handle login attempt
     */
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $passwordHash = config('escalation.tickets_auth.password_hash');

        if (empty($passwordHash)) {
            return back()->withErrors([
                'password' => 'Ticket authentication is not configured. Please run: php artisan escalation:set-password',
            ]);
        }

        if (Hash::check($request->password, $passwordHash)) {
            session(['escalation_tickets_authenticated' => true]);
            return redirect()->route('escalation.tickets.index');
        }

        return back()->withErrors([
            'password' => 'Invalid password.',
        ])->withInput();
    }

    /**
     * Logout
     */
    public function logout()
    {
        session()->forget('escalation_tickets_authenticated');
        return redirect()->route('escalation.login')->with('success', 'Logged out successfully.');
    }
}
