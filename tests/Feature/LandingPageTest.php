<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\LandingPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_for_an_event(): void
    {
        $event = Event::factory()->create([
            'slug' => 'ccs-2026',
            'name_en' => 'Content Creators Summit',
            'status' => EventStatus::Published,
        ]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertStatus(200);
        $response->assertSee('Content Creators Summit');
    }

    public function test_landing_page_shows_about_content_when_present(): void
    {
        $event = Event::factory()->create();
        LandingPageContent::factory()->for($event)->create([
            'section' => LandingPageSection::About,
            'field_key' => 'body',
            'value_en' => 'Where digital creators meet.',
        ]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('Where digital creators meet.');
    }

    public function test_about_section_omitted_when_no_content(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertDontSee('id="about"', false);
    }
}
