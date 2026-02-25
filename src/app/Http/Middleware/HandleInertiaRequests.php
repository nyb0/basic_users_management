<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? 
                    array_merge($request->user()->toArray(), [
                        'isVerified' => optional($request->user())->hasVerifiedEmail(),
                        'isAdmin' => optional($request->user())->isAdmin(),
                        'isModerator' => optional($request->user())->isModerator(),
                        'isAdminOrModerator' => optional($request->user())->isAdminOrModerator(),
                    ])
                    : null,
            ],
            // Flash-messages from session, passed with methods .with() or .flash()
            'flash' => function () use ($request) {
            // Get keys from session flash
            $oldKeys = $request->session()->get('_flash.old', []);
            $newKeys = $request->session()->get('_flash.new', []);
            $allFlashKeys = array_unique(array_merge($oldKeys, $newKeys));

            return count($allFlashKeys) > 0
                ? collect($request->session()->all())->only($allFlashKeys)->toArray()
                : [];
            },
        ];
    }
}
