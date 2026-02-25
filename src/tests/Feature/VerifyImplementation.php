<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyImplementation extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that our implementation works correctly.
     */
    public function test_implementation()
    {
        // Test 1: Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => UserRoles::ADMIN,
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertEquals(UserRoles::ADMIN, $admin->role);

        // Test 2: Create regular user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => UserRoles::USER,
        ]);

        $this->assertTrue($user->isUser());
        $this->assertEquals(UserRoles::USER, $user->role);

        // Test 3: Create moderator user
        $moderator = User::create([
            'name' => 'Moderator User',
            'email' => 'moderator@example.com',
            'password' => 'password123',
            'role' => UserRoles::MODERATOR,
        ]);

        $this->assertTrue($moderator->isModerator());
        $this->assertEquals(UserRoles::MODERATOR, $moderator->role);

        // Test 4: Test default role
        $defaultUser = User::create([
            'name' => 'Default User',
            'email' => 'default@example.com',
            'password' => 'password123',
        ]);

        $this->assertEquals(UserRoles::USER, $defaultUser->role);

        // Test 5: Test role-based access
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($moderator->isAdmin());
        
        $this->assertFalse($admin->isModerator());
        $this->assertTrue($moderator->isModerator());
        $this->assertFalse($user->isModerator());

        $this->assertFalse($admin->isUser());
        $this->assertFalse($moderator->isUser());
        $this->assertTrue($user->isUser());

        echo "✅ All tests passed! User roles implementation is working correctly.\n";
    }
}