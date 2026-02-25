<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // If user is not authenticated, handle verification_email parameter
        if(!$user) {
            $validator = Validator::make($request->all(), [
                'verification_email' => [
                    'required',
                    'email',
                    'exists:users,email',
                ],
            ]);
            if ($validator->fails()) {
                return redirect()->route('welcome')->withErrors($validator);
            }
            // Find user by email
            $user = User::where('email', $request->verification_email)->first();
        }

        if ($user) {
            if ($user->hasVerifiedEmail()) {
                return redirect()->intended(route('dashboard', absolute: false));
            }
            $user->sendEmailVerificationNotification();
        }

        return redirect()->back()->with('success', 'We have emailed your account verification link.');
    }
}
