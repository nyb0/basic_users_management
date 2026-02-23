<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserDeleteRequest extends FormRequest
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

        $rules = [];

        // Password validation required for self-deletion
        if ($currentUser->id === $user->id) {
            $rules['password'] = ['required', 'current_password'];
        }

        return $rules;
    }
}