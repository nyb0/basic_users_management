<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\AuthenticationException;
use App\Http\Requests\Auth\CustomEmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(CustomEmailVerificationRequest $request): RedirectResponse
    {
        $authUser = $request->user();
        $userByID = User::find($request->route('id'));
        
        // User not found by ID from email or hash missmatch
        if (! $userByID || ! hash_equals(sha1($userByID->getEmailForVerification()), (string) $request->route('hash'))) {
            // If no authenticated user - redirect at welcome page with error message
            if (! $authUser || ! $authUser->hasVerifiedEmail()) {
            $message = 'Oops! Your link doesn\'t seem to be working or it has been expired.';
                return redirect()->route('welcome')->with([
                    'signatureError' => $message,
                    'showVerificationModal' => true,
                    ]);
            }
            // If authenticated - redirect to dashboard
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // If no authenticated user - login
        if (! $authUser) {
            Auth::login($userByID);
        }
        // If authenticated user isn't match with user found by ID from email - logout current, authenticate mailed user
        elseif ($authUser && $authUser->getKey() !== $userByID->getKey()) {
            Auth::logout();
            Auth::login($userByID);
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }
        
        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Your email has been verified successfully!');
    }
}
