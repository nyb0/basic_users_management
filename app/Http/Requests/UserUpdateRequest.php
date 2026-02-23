<?php

namespace App\Http\Requests;

use App\Enums\UserRoles;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $currentUser = $this->user();
        $user = $this->route('user');

        $rules = [
            'name' => 'required|string|max:255',
            'phone_number' => ['nullable', 'phone:US,INTERNATIONAL', Rule::unique('users')->ignore($user->id)],
        ];

        // Admin can change role, moderator cannot
        if ($currentUser->isAdmin()) {
            $rules['role'] = ['required', Rule::in([UserRoles::ADMIN->value, UserRoles::MODERATOR->value, UserRoles::USER->value])];
        }

        // Email can be changed by:
        // - Admin (for any user)
        // - Moderator (for regular users only, not for admins or other moderators)
        // - User editing their own account
        if ($this->canEditEmail($currentUser, $user)) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)];
        }

        return $rules;
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
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone_number.phone' => 'The phone number field must be a valid phone number.',
        ];
    }
}