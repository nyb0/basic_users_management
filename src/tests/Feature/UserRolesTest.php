<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin user can be created and has correct role.
     */
    public function test_admin_user_can_be_created()
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => UserRoles::ADMIN,
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isModerator());
        $this->assertFalse($user->isUser());
        $this->assertEquals(UserRoles::ADMIN, $user->role);
    }

    /**
     * Test that regular user can be created and has correct role.
     */
    public function test_regular_user_can_be_created()
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => UserRoles::USER,
        ]);

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isModerator());
        $this->assertTrue($user->isUser());
        $this->assertEquals(UserRoles::USER, $user->role);
    }

    /**
     * Test that moderator user can be created and has correct role.
     */
    public function test_moderator_user_can_be_created()
    {
        $user = User::create([
            'name' => 'Moderator User',
            'email' => 'moderator@example.com',
            'password' => 'password123',
            'role' => UserRoles::MODERATOR,
        ]);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isModerator());
        $this->assertFalse($user->isUser());
        $this->assertEquals(UserRoles::MODERATOR, $user->role);
    }

    /**
     * Test that user role defaults to 'user'.
     */
    public function test_user_role_defaults_to_user()
    {
        $user = User::create([
            'name' => 'Default User',
            'email' => 'default@example.com',
            'password' => 'password123',
        ]);

        $this->assertEquals(UserRoles::USER, $user->role);
    }

    /**
     * Test that admin can access user management routes.
     */
    public function test_admin_can_access_user_management()
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);

        $response = $this->actingAs($admin)->get(route('users.index'));
        $response->assertStatus(200);
    }

    /**
     * Test that regular user cannot access user management routes.
     */
    public function test_regular_user_cannot_access_user_management()
    {
        $user = User::factory()->create(['role' => UserRoles::USER]);

        $response = $this->actingAs($user)->get(route('users.index'));
        $response->assertStatus(403);
    }

    /**
     * Test that moderator can access user management routes with limited visibility.
     */
    public function test_moderator_can_access_user_management()
    {
        $moderator = User::factory()->create(['role' => UserRoles::MODERATOR]);

        $response = $this->actingAs($moderator)->get(route('users.index'));
        $response->assertStatus(200);

        // Verify that admin users are not visible to moderators
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);
        $response->assertDontSee($admin->email);
    }
}