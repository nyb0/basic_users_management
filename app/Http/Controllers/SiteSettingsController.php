<?php

namespace App\Http\Controllers;

use App\Services\SiteSettingsService;
use App\Http\Requests\SiteSettingUpdateRequest;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingsController extends Controller
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
     * Display the Site Settings page.
     * Note: Route access is controlled by 'role:admin' middleware.
     */
    public function index(): Response
    {
        $aboutUsText = $this->settingsService->getAboutUs();
        $verifyOnMailChanged = $this->settingsService->getVerifyOnMailChanged();

        return Inertia::render('SiteSettings/Index', [
            'aboutUsText' => $aboutUsText,
            'verifyOnMailChanged' => $verifyOnMailChanged,
        ]);
    }

    /**
     * Update the About Us text.
     * Note: Route access is controlled by 'role:admin' middleware.
     */
    public function updateAboutUs(SiteSettingUpdateRequest $request)
    {
        $text = $request->input('about_us_text') ?? ''; 
        $this->settingsService->setAboutUs($text);

        return redirect()->route('site-settings.index')
            ->with('success', 'About Us text updated successfully.');
    }

    /**
     * Update the authentication settings.
     * Note: Route access is controlled by 'role:admin' middleware.
     */
    public function updateAuthentication(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'verify_on_mail_changed' => ['required', 'boolean'],
        ]);

        $this->settingsService->setVerifyOnMailChanged((bool) $request->input('verify_on_mail_changed'));

        return redirect()->route('site-settings.index')
            ->with('success', 'Authentication settings updated successfully.');
    }

    /**
     * Display the public About Us page.
     */
    public function aboutUs(): Response
    {
        $aboutUsText = $this->settingsService->getAboutUs();

        return Inertia::render('AboutUs', [
            'aboutUsText' => $aboutUsText,
            'canLogin' => route('login'),
            'canRegister' => route('register'),
        ]);
    }
}