<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_links_to_the_ticket_and_about_sections(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('href="#about"', false);
        $response->assertSee('href="#tickets"', false);
        $response->assertSee('Request Ticket');
    }

    public function test_footer_renders_with_event_name(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published, 'name_en' => 'Content Creators Summit']);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('Content Creators Summit');
        $response->assertSee('All rights reserved.');
    }
}
