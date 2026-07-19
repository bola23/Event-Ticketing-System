<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\Faq;
use App\Models\LandingPageContent;
use App\Models\Speaker;
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

    public function test_speakers_section_lists_speaker_names(): void
    {
        $event = Event::factory()->create();
        Speaker::factory()->for($event)->create(['name_en' => 'Jane Creator']);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('Jane Creator');
    }

    public function test_partners_section_omitted_when_no_sponsors(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertDontSee('id="partners"', false);
    }

    public function test_faq_section_lists_questions(): void
    {
        $event = Event::factory()->create();
        Faq::factory()->for($event)->create(['question_en' => 'How do I pay?']);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('How do I pay?');
    }

    public function test_location_section_shows_venue_intro(): void
    {
        $event = Event::factory()->create();
        LandingPageContent::factory()->for($event)->create([
            'section' => LandingPageSection::Location,
            'field_key' => 'intro',
            'value_en' => 'Held at the Convention Center.',
        ]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('Held at the Convention Center.');
    }
}
