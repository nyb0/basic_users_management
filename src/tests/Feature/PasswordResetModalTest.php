<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetModalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that password reset redirects to welcome page with modal data.
     */
    public function test_password_reset_redirects_to_welcome_with_modal_data()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Generate a password reset token
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));

        // Should redirect to welcome page
        $response->assertRedirect(route('welcome'));

        // Check session has flash data for modal
        $response->assertSessionHas('showResetPasswordModal', true);
        $response->assertSessionHas('resetToken', $token);
        $response->assertSessionHas('resetEmail', $user->email);
    }

    /**
     * Test that password reset form submission works correctly.
     */
    public function test_password_reset_form_submission_works()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'oldpassword123',
        ]);

        // Generate a password reset token
        $token = Password::createToken($user);

        $newPassword = 'newpassword123';

        $response = $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        // Should redirect to welcome page
        $response->assertRedirect(route('welcome'));

        // Check session has success message
        $response->assertSessionHas('success');

        // Password should be updated
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }

    /**
     * Test that password reset form validation works.
     */
    public function test_password_reset_form_validation_works()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Generate a password reset token
        $token = Password::createToken($user);

        $response = $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short', // Too short
            'password_confirmation' => 'short',
        ]);

        // Should redirect back with errors
        $response->assertSessionHasErrors(['password']);
    }
}