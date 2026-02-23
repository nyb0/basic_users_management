<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;

class CustomEmailVerificationRequest extends EmailVerificationRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        return true;
    }
}
