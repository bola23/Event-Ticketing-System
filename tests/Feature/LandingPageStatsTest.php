<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\LandingPageContent;
use App\Models\Speaker;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_section_shows_live_and_manual_values(): void
    {
        $event = Event::factory()->create();
        Speaker::factory()->for($event)->create();
        Workshop::factory()->for($event)->create();
        LandingPageContent::factory()->for($event)->create([
            'section' => LandingPageSection::Stats, 'field_key' => 'attendees_count', 'value_en' => '4,200+',
        ]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('id="stats"', false);
        $response->assertSee('4,200+');

        $html = view('landing.partials.stats', ['event' => $event->load('speakers', 'workshops', 'landingPageContent')])->render();
        $this->assertStringContainsString('data-stat-value="speakers">1<', $html);
        $this->assertStringContainsString('data-stat-value="workshops">1<', $html);
    }

    public function test_stats_section_omitted_when_no_data(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertDontSee('id="stats"', false);
    }
}
