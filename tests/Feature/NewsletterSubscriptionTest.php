<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_subscribe(): void
    {
        $event = Event::factory()->create();

        $response = $this->post(route('newsletter.store', $event), ['email' => 'fan@example.com']);

        $response->assertRedirect(route('landing.show', $event).'#newsletter');
        $this->assertDatabaseHas('newsletter_subscribers', ['event_id' => $event->id, 'email' => 'fan@example.com']);
    }

    public function test_subscribing_requires_a_valid_email(): void
    {
        $event = Event::factory()->create();

        $response = $this->post(route('newsletter.store', $event), ['email' => 'not-an-email']);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_duplicate_subscription_is_idempotent_not_an_error(): void
    {
        $event = Event::factory()->create();
        NewsletterSubscriber::factory()->for($event)->create(['email' => 'fan@example.com']);

        $response = $this->post(route('newsletter.store', $event), ['email' => 'fan@example.com']);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_newsletter_section_renders_the_form(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('id="newsletter"', false);
        $response->assertSee('action="'.route('newsletter.store', $event).'"', false);
    }
}
