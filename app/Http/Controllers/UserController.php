<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRoles;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserDeleteRequest;
use App\Services\UsersService;
use App\Services\SiteSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    protected UsersService $usersService;
    protected SiteSettingsService $settingsService;

    /**
     * @param UsersService $usersService
     * @param SiteSettingsService $settingsService
     */
    public function __construct(UsersService $usersService, SiteSettingsService $settingsService) {
        $this->usersService = $usersService;
        $this->settingsService = $settingsService;
    }
    /**
     * Display a listing of the users.
     * Note: Route access is controlled by 'role:admin,moderator' middleware.
     */
    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'searchRoles' => UserRoles::authSearchCases(),
            'editRoles' => UserRoles::authEditCases(),
            'verifyOnMailChanged' => $this->settingsService->getVerifyOnMailChanged(),
        ]);
    }

    /**
     * Return paginated users as JSON for the DataTable component (no URL state).
     * Note: Route access is controlled by 'role:admin,moderator' middleware.
     */
    public function search(): JsonResponse
    {
        $currentUser = auth()->user();
        $searchParams = $this->getSearchParams();
        $users = $this->usersService->getUsersList($currentUser, $searchParams);

        return response()->json($users);
    }

    /**
     * Get search parameters from request.
     */
    private function getSearchParams(): array
    {
        return [
            'name' => request('name'),
            'email' => request('email'),
            'role' => request('role'),
            'isVerified' => request('isVerified'),
            'createdAtFrom' => request('createdAtFrom'),
            'createdAtTo' => request('createdAtTo'),
        ];
    }

    /**
     * Store a newly created user in storage.
     * Note: Route access is controlled by 'role:admin,moderator' middleware.
     */
    public function store(UserCreateRequest $request)
    {
        $currentUser = auth()->user();
        try {
            $user = $this->usersService->createUser($request->validated(), $currentUser);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully. Verification email sent.');
    }

    /**
     * Update the specified user in storage.
     * Note: Route access is controlled by 'role:admin,moderator' middleware.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $currentUser = auth()->user();
        $verifyOnMailChanged = $this->settingsService->getVerifyOnMailChanged();

        try {
            $result = $this->usersService->updateUser($user, $request->validated(), $currentUser, $verifyOnMailChanged);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Build appropriate success message
        $successMessage = 'User updated successfully.';
        if ($result['emailChanged'] && $result['verificationSent']) {
            if ($result['isOwnAccount']) {
                $successMessage = 'Your email has been updated. Please verify your new email address.';
            } else {
                $successMessage = 'User updated successfully. A verification email has been sent to the new email address.';
            }
        }

        return redirect()->route('users.index')
            ->with('success', $successMessage);
    }

    /**
     * Remove the specified user from storage.
     * Note: Route access is controlled by 'role:admin,moderator' middleware.
     */
    public function destroy(UserDeleteRequest $request, User $user)
    {
        $currentUser = auth()->user();
        try {
            $this->usersService->deleteUser($user, $currentUser, $request->input('password'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e->getMessage() === 'SELF_DELETION_REDIRECT') {
                return redirect()->route('welcome')->with('success', 'Your account has been deleted.');
            }
            throw $e;
        }

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        // This method is no longer needed for inline forms
        abort(404);
    }

    /**
     * Display the specified user.
     */
    public function show()
    {
        // This method is no longer needed for inline forms
        abort(404);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit()
    {
        // This method is no longer needed for inline forms
        abort(404);
    }
}
