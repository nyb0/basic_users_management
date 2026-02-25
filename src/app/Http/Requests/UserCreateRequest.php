<?php

namespace App\Http\Requests;

use App\Enums\UserRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCreateRequest extends FormRequest
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

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'phone_number' => ['nullable', 'phone:US,INTERNATIONAL', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
        ];

        // Admins can assign any role, moderators can only create regular users
        if ($currentUser->isModerator()) {
            $rules['role'] = ['required', Rule::in([UserRoles::USER->value])];
        } else {
            $rules['role'] = ['required', Rule::in([UserRoles::ADMIN->value, UserRoles::MODERATOR->value, UserRoles::USER->value])];
        }

        return $rules;
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