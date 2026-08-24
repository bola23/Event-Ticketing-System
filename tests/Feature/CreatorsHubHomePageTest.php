<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class CreatorsHubHomePageTest extends TestCase
{
    public function test_home_page_renders_with_key_sections(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="hero"', false);
        $response->assertSee('id="about"', false);
        $response->assertSee('id="events"', false);
        $response->assertSee('id="community"', false);
        $response->assertSee('id="partners"', false);
        $response->assertSee('id="contact"', false);
    }

    public function test_home_page_navigation_links_to_the_events_index(): void
    {
        $response = $this->get(route('home'));

        $response->assertSee('href="'.route('events.index').'"', false);
    }

    public function test_events_index_shows_a_coming_soon_state_when_no_events_exist(): void
    {
        $response = $this->get(route('events.index'));

        $response->assertOk();
        $response->assertSee(__('Coming Soon'));
    }
}
