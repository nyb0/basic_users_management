<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        abort(404);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                'phone_number' => ['nullable', 'phone:US,INTERNATIONAL', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ], [
                'phone_number.phone' => 'The phone number field must be a valid phone number.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return all validation errors instead of just the last one
            if ($request->wantsJson()) {
                return response()->json([
                    'errors' => $e->errors(),
                    'message' => 'Registration failed due to validation errors.'
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // Automatically log in the user after registration
        Auth::login($user);

        // Determine redirect based on user role (new users are always 'user' role by default)
        $redirectRoute = 'profile.edit';

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Successfully registered. Please check your email to verify your account.',
                'redirect' => route($redirectRoute, absolute: false)
            ]);
        }

        return redirect(route($redirectRoute, absolute: false));
    }
}
