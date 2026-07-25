<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriberIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_subscribers(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        NewsletterSubscriber::factory()->for($event)->create(['email' => 'fan@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.events.newsletter-subscribers.index', $event));

        $response->assertOk();
        $response->assertSee('fan@example.com');
    }

    public function test_admin_index_shows_empty_state_with_no_subscribers(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.events.newsletter-subscribers.index', $event));

        $response->assertOk();
        $response->assertSee('No subscribers yet.');
    }
}
