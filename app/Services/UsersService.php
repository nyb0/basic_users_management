<?php

namespace App\Services;

use App\Models\User;
use App\Enums\UserRoles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class UsersService
{
    /**
     * Get paginated list of users based on current user's role and search parameters.
     * 
     * @param User $currentUser
     * @param array $searchParams
     */
    public function getUsersList(User $currentUser, array $searchParams = [])
    {
        // Admin sees all users
        $query = User::select('id', 'name', 'email', 'phone_number', 'role', 'email_verified_at', 'created_at');
        if ($currentUser->isAdmin()) {
            $query->search($searchParams);
            return $query->orderBy('created_at', 'desc')->paginate(10);
        }

        // Moderator sees only moderators and regular users (no admins)
        $query->whereIn('role', [UserRoles::MODERATOR->value, UserRoles::USER->value]);
        $query->search($searchParams);
        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @param User $currentUser
     * @return User
     * @throws ValidationException
     */
    public function createUser(array $data, User $currentUser): User
    {
        // Validate role permissions
        $this->validateRolePermissions($data['role'], $currentUser);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        return $user;
    }

    /**
     * Update an existing user.
     *
     * @param User $user
     * @param array $data
     * @param User $currentUser
     * @param bool $verifyOnMailChanged
     * @return array{user: User, emailChanged: bool, verificationSent: bool}
     * @throws ValidationException
     */
    public function updateUser(User $user, array $data, User $currentUser, bool $verifyOnMailChanged = false): array
    {
        // Cannot edit admin and moderator users unless you are the admin or editing your own account
        if ($user->isAdminOrModerator() && ! $currentUser->isAdmin() && $user->id !== $currentUser->id) {
            throw ValidationException::withMessages([
                'permission' => 'Cannot edit other moderator users.',
            ]);
        }

        $updateData = [
            'name' => $data['name'],
            'phone_number' => $data['phone_number'] ?? null,
        ];

        // Only admin can change role
        if ($currentUser->isAdmin() && isset($data['role'])) {
            $updateData['role'] = $data['role'];
        }

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        // Track email changes
        $emailChanged = false;
        $verificationSent = false;
        $isOwnAccount = $currentUser->id === $user->id;

        // Handle email changes
        if (isset($data['email']) && $data['email'] !== $user->email) {
            // Check permission to edit email
            if (!$this->canEditEmail($currentUser, $user)) {
                throw ValidationException::withMessages([
                    'email' => 'You do not have permission to change this user\'s email.',
                ]);
            }

            $emailChanged = true;
            $updateData['email'] = $data['email'];

            // If verification on mail change is enabled
            if ($verifyOnMailChanged) {
                $updateData['email_verified_at'] = null;
                $verificationSent = true;
            }
        }

        $user->update($updateData);

        // Send verification notification after update (so email is changed)
        if ($verificationSent) {
            $user->sendEmailVerificationNotification();
        }

        return [
            'user' => $user,
            'emailChanged' => $emailChanged,
            'verificationSent' => $verificationSent,
            'isOwnAccount' => $isOwnAccount,
        ];
    }

    /**
     * Check if the current user can edit the target user's email.
     */
    private function canEditEmail(User $currentUser, User $targetUser): bool
    {
        // User can always edit their own email
        if ($currentUser->id === $targetUser->id) {
            return true;
        }

        // Admin can edit anyone's email
        if ($currentUser->isAdmin()) {
            return true;
        }

        // Moderator can edit regular users' emails only
        if ($currentUser->isModerator() && !$targetUser->isAdminOrModerator()) {
            return true;
        }

        return false;
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @param User $currentUser
     * @param string|null $password
     * @return void
     * @throws ValidationException
     */
    public function deleteUser(User $user, User $currentUser, ?string $password = null): void
    {
        // Check if user is deleting their own account
        $isSelfDeletion = $currentUser->id === $user->id;

        if ($isSelfDeletion) {
            // Validate password for self-deletion
            if (! $password || ! Hash::check($password, $currentUser->password)) {
                throw ValidationException::withMessages([
                    'password' => 'The provided password was incorrect.',
                ]);
            }

            // Logout, delete user, and invalidate session
            Auth::logout();
            $user->delete();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            
            // Redirect to home page for self-deletion
            // This will be handled by the controller
            throw new \Exception('SELF_DELETION_REDIRECT');
        }

        // Only Admins can delete other admin users
        if ($user->isAdmin() && !$currentUser->isAdmin()) {
            throw ValidationException::withMessages([
                'permission' => 'Cannot delete admin users.',
            ]);
        }

        // Moderators cannot delete other moderators
        if ($currentUser->isModerator() && $user->isModerator()) {
            throw ValidationException::withMessages([
                'permission' => 'Cannot delete moderator users.',
            ]);
        }

        $user->delete();
    }

    /**
     * Validate role permissions for user creation.
     *
     * @param string $role
     * @param User $currentUser
     * @return void
     * @throws ValidationException
     */
    private function validateRolePermissions(string $role, User $currentUser): void
    {
        // Admins can assign any role
        if ($currentUser->isAdmin()) {
            return;
        }

        // Moderators can only create regular users
        if ($currentUser->isModerator() && $role !== UserRoles::USER->value) {
            throw ValidationException::withMessages([
                'permission' => 'Moderators can only create regular users.',
            ]);
        }
    }
}