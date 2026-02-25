<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(404);
    }

    public function test_password_can_be_confirmed_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        
        // Verify password was confirmed by checking session
        $this->assertNotNull($response->getSession()->get('auth.password_confirmed_at'));
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['password']);
        
        // Verify password was not confirmed
        $this->assertNull($response->getSession()->get('auth.password_confirmed_at'));
    }

    public function test_password_confirmation_works_for_profile_deletion(): void
    {
        $user = User::factory()->create();

        // First confirm password
        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Now try to delete profile with confirmed password
        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'Your account has been deleted.');

        // Verify user was deleted
        $this->assertNull(User::find($user->id));
    }

    public function test_password_confirmation_works_for_user_deletion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userToDelete = User::factory()->create();

        // First confirm password for admin
        $response = $this->actingAs($admin)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Now try to delete user with confirmed password
        $response = $this->actingAs($admin)->delete("/users/{$userToDelete->id}", [
            'password' => 'password',
        ]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'User deleted successfully.');

        // Verify user was deleted
        $this->assertNull(User::find($userToDelete->id));
    }
}
