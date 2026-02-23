<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        try {
            // First validate all fields
            $request->validate($request->rules());
            
            // Then attempt authentication
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return all validation errors instead of just the last one
            if ($request->wantsJson()) {
                return response()->json([
                    'errors' => $e->errors(),
                    'message' => 'The provided credentials do not match our records.'
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }

        // Check if user's email is verified
        if (! $request->user()->hasVerifiedEmail()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Please verify your email address before logging in.',
                    'redirect' => route('welcome', absolute: false),
                ], 422);
            }
            
            return redirect()->route('welcome');
        }

        $request->session()->regenerate();

        // Determine redirect based on user role
        $redirectRoute = match ($request->user()->role->value) {
            'user' => 'profile.edit',
            'moderator', 'admin' => 'dashboard',
            default => 'dashboard',
        };

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Successfully logged in.',
                'redirect' => route($redirectRoute, absolute: false)
            ]);
        }

        return redirect()->intended(route($redirectRoute, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
