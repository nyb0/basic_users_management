<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Faq;
use App\Models\SiteSetting;
use App\Enums\UserRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    // ========================================================================
    // Site Settings Page Access Tests
    // ========================================================================

    public function test_admin_can_access_site_settings_page(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);

        $response = $this->actingAs($admin)->get(route('site-settings.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('SiteSettings/Index')
            ->has('aboutUsText')
        );
    }

    public function test_moderator_cannot_access_site_settings_page(): void
    {
        $moderator = User::factory()->create(['role' => UserRoles::MODERATOR]);

        $response = $this->actingAs($moderator)->get(route('site-settings.index'));

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_access_site_settings_page(): void
    {
        $user = User::factory()->create(['role' => UserRoles::USER]);

        $response = $this->actingAs($user)->get(route('site-settings.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_site_settings_page(): void
    {
        $response = $this->get(route('site-settings.index'));

        $response->assertRedirect(route('welcome'));
    }

    // ========================================================================
    // About Us Tests
    // ========================================================================

    public function test_admin_can_update_about_us_text(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);

        $response = $this->actingAs($admin)->put(route('site-settings.about-us.update'), [
            'about_us_text' => 'This is our company story.',
        ]);

        $response->assertRedirect(route('site-settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('site_settings', [
            'key' => 'about_us_text',
            'value' => 'This is our company story.',
        ]);
    }

    public function test_public_about_us_page_displays_content(): void
    {
        SiteSetting::setAboutUs('Our amazing company story.');

        $response = $this->get(route('about-us'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('AboutUs')
            ->where('aboutUsText', 'Our amazing company story.')
        );
    }

    public function test_public_about_us_page_shows_empty_state(): void
    {
        $response = $this->get(route('about-us'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('AboutUs')
            ->where('aboutUsText', null)
        );
    }

    // ========================================================================
    // FAQ CRUD Tests
    // ========================================================================

    public function test_admin_can_create_faq(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);

        $response = $this->actingAs($admin)->post(route('faqs.store'), [
            'question' => 'What is this?',
            'answer' => 'This is an answer.',
            'priority' => 10,
        ]);

        $response->assertRedirect(route('site-settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('faqs', [
            'question' => 'What is this?',
            'answer' => 'This is an answer.',
            'priority' => 10,
        ]);
    }

    public function test_admin_can_update_faq(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);
        $faq = Faq::factory()->create();

        $response = $this->actingAs($admin)->put(route('faqs.update', $faq), [
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
            'priority' => 5,
        ]);

        $response->assertRedirect(route('site-settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
            'priority' => 5,
        ]);
    }

    public function test_admin_can_delete_faq(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);
        $faq = Faq::factory()->create();

        $response = $this->actingAs($admin)->delete(route('faqs.destroy', $faq));

        $response->assertRedirect(route('site-settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('faqs', [
            'id' => $faq->id,
        ]);
    }

    public function test_moderator_cannot_create_faq(): void
    {
        $moderator = User::factory()->create(['role' => UserRoles::MODERATOR]);

        $response = $this->actingAs($moderator)->post(route('faqs.store'), [
            'question' => 'What is this?',
            'answer' => 'This is an answer.',
            'priority' => 10,
        ]);

        $response->assertStatus(403);
    }

    public function test_moderator_cannot_update_faq(): void
    {
        $moderator = User::factory()->create(['role' => UserRoles::MODERATOR]);
        $faq = Faq::factory()->create();

        $response = $this->actingAs($moderator)->put(route('faqs.update', $faq), [
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
            'priority' => 5,
        ]);

        $response->assertStatus(403);
    }

    public function test_moderator_cannot_delete_faq(): void
    {
        $moderator = User::factory()->create(['role' => UserRoles::MODERATOR]);
        $faq = Faq::factory()->create();

        $response = $this->actingAs($moderator)->delete(route('faqs.destroy', $faq));

        $response->assertStatus(403);
    }

    public function test_faq_search_endpoint_returns_paginated_results(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);
        Faq::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get(route('faqs.search'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'current_page',
            'last_page',
            'per_page',
            'total',
        ]);
    }

    public function test_faq_search_filters_by_question(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);
        Faq::factory()->create(['question' => 'How to login?']);
        Faq::factory()->create(['question' => 'What is the pricing?']);
        Faq::factory()->create(['question' => 'How to reset password?']);

        $response = $this->actingAs($admin)->get(route('faqs.search', ['search' => 'login']));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data['data']);
        $this->assertEquals('How to login?', $data['data'][0]['question']);
    }

    public function test_faq_search_filters_by_answer(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);
        Faq::factory()->create(['question' => 'Question 1', 'answer' => 'You can login with your email.']);
        Faq::factory()->create(['question' => 'Question 2', 'answer' => 'The pricing is monthly.']);
        Faq::factory()->create(['question' => 'Question 3', 'answer' => 'Click the reset link.']);

        $response = $this->actingAs($admin)->get(route('faqs.search', ['search' => 'pricing']));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data['data']);
        $this->assertEquals('Question 2', $data['data'][0]['question']);
    }

    // ========================================================================
    // Public FAQ Page Tests
    // ========================================================================

    public function test_public_faq_page_displays_faqs_ordered_by_priority(): void
    {
        $faq1 = Faq::factory()->create(['priority' => 10, 'question' => 'Low priority question?']);
        $faq2 = Faq::factory()->create(['priority' => 1, 'question' => 'High priority question?']);
        $faq3 = Faq::factory()->create(['priority' => 5, 'question' => 'Medium priority question?']);

        $response = $this->get(route('faq.public'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Faq')
            ->has('faqs', 3)
            ->where('faqs.0.question', 'High priority question?')
            ->where('faqs.1.question', 'Medium priority question?')
            ->where('faqs.2.question', 'Low priority question?')
        );
    }

    public function test_public_faq_page_shows_empty_state(): void
    {
        $response = $this->get(route('faq.public'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Faq')
            ->has('faqs', 0)
        );
    }

    // ========================================================================
    // Validation Tests
    // ========================================================================

    public function test_faq_create_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);

        $response = $this->actingAs($admin)->post(route('faqs.store'), []);

        $response->assertSessionHasErrors(['question', 'answer']);
    }

    public function test_faq_create_validates_max_lengths(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);

        $response = $this->actingAs($admin)->post(route('faqs.store'), [
            'question' => str_repeat('a', 1001),
            'answer' => str_repeat('a', 5001),
        ]);

        $response->assertSessionHasErrors(['question', 'answer']);
    }

    public function test_faq_create_validates_priority_range(): void
    {
        $admin = User::factory()->create(['role' => UserRoles::ADMIN]);

        $response = $this->actingAs($admin)->post(route('faqs.store'), [
            'question' => 'Test question?',
            'answer' => 'Test answer.',
            'priority' => 10000,
        ]);

        $response->assertSessionHasErrors(['priority']);
    }
}