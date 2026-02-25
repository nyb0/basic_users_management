<?php

namespace App\Http\Middleware;

use App\Enums\UserRoles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for role-based access control.
 *
 * Supports single or multiple roles:
 * - role:admin
 * - role:admin,moderator
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->handleUnauthenticated($request);
        }

        if (! $this->hasRequiredRole($user, $roles)) {
            return $this->handleUnauthorized($request, $roles);
        }

        return $next($request);
    }

    /**
     * Check if the authenticated user has any of the required roles.
     */
    protected function hasRequiredRole($user, array $roles): bool
    {
        if (empty($roles)) {
            return false;
        }

        foreach ($roles as $role) {
            if ($this->userHasRole($user, $role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has a specific role.
     */
    protected function userHasRole($user, string $role): bool
    {
        $userRole = $user->role;

        // Handle both enum and string comparisons
        if ($userRole instanceof UserRoles) {
            return $userRole->value === $role || $userRole->name === ucfirst($role);
        }

        return $userRole === $role;
    }

    /**
     * Handle unauthenticated users.
     */
    protected function handleUnauthenticated(Request $request): Response
    {
        // Return 401 for API/Inertia requests, redirect for regular web
        if ($request->expectsJson() || $this->isInertiaRequest($request)) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return redirect()->route('welcome')->with('showLoginModal', true);
    }

    /**
     * Handle unauthorized access attempts.
     * Always returns 403 Forbidden for security - redirects can leak route existence.
     */
    protected function handleUnauthorized(Request $request, array $requiredRoles): Response
    {
        if ($request->expectsJson() || $this->isInertiaRequest($request)) {
            return response()->json([
                'message' => 'Forbidden. You do not have the required role(s) to access this resource.',
                'required_roles' => $requiredRoles,
            ], 403);
        }

        // For regular web requests, also return 403 to avoid leaking route information
        abort(403, 'You do not have permission to access this page.');
    }

    /**
     * Check if the request is an Inertia request.
     */
    protected function isInertiaRequest(Request $request): bool
    {
        return $request->header('X-Inertia') !== null;
    }
}
