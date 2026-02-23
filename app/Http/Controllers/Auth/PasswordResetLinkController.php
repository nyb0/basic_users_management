<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        abort(404);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $status = Password::sendResetLink(
                $request->only('email')
            ); 

            if ($status == Password::RESET_LINK_SENT) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => __($status),
                    ]);
                }
                return back()->with('status', __($status));
            }

            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return all validation errors instead of just the last one
            if ($request->wantsJson()) {
                return response()->json([
                    'errors' => $e->errors(),
                    'message' => 'Failed to send password reset link.'
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
