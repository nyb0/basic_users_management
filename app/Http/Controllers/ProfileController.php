<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\SiteSettingsService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    protected SiteSettingsService $settingsService;

    /**
     * @param SiteSettingsService $settingsService
     */
    public function __construct(SiteSettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'verifyNotificationStatus' => session('verify_notification_status'),
            'verifyOnMailChanged' => $this->settingsService->getVerifyOnMailChanged(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $verifyOnMailChanged = $this->settingsService->getVerifyOnMailChanged();
        $emailChanged = $request->user()->email !== $request->validated()['email'];

        $request->user()->fill($request->validated());

        if ($emailChanged) {
            if ($verifyOnMailChanged) {
                $request->user()->email_verified_at = null;
            }
        }

        $request->user()->save();

        // Send verification email if email changed and setting is enabled
        if ($emailChanged && $verifyOnMailChanged) {
            $request->user()->sendEmailVerificationNotification();
            return Redirect::route('profile.edit')
                ->with('verify_notification_status', 'verification-link-sent')
                ->with('success', 'Your email has been updated. Please verify your new email address.');
        }

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome')->with('success', 'Your account has been deleted.');
    }
}
