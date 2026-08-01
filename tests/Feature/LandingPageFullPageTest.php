<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\LandingPageSection;
use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\Faq;
use App\Models\GalleryPhoto;
use App\Models\LandingPageContent;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\Testimonial;
use App\Models\TicketType;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageFullPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_fully_populated_event_renders_every_section_in_order(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        Speaker::factory()->for($event)->create();
        Workshop::factory()->for($event)->create();
        AgendaItem::factory()->for($event)->create();
        TicketType::factory()->for($event)->create();
        Sponsor::factory()->for($event)->create();
        Faq::factory()->for($event)->create();
        Testimonial::factory()->for($event)->create();
        GalleryPhoto::factory()->for($event)->create();
        LandingPageContent::factory()->for($event)->create(['section' => LandingPageSection::About, 'field_key' => 'body']);
        LandingPageContent::factory()->for($event)->create(['section' => LandingPageSection::Location, 'field_key' => 'intro']);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertOk();
        $response->assertSeeInOrder([
            'id="hero"', 'id="about"', 'id="speakers"', 'id="workshops"',
            'id="tickets"', 'id="awards"', 'id="gallery"',
            'id="testimonials"', 'id="partners"', 'id="faq"', 'id="location"',
            'id="contact"', 'id="newsletter"',
        ], false);
    }

    public function test_minimal_event_still_renders_ok(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);

        $response = $this->get(route('landing.show', $event));

        $response->assertOk();
        $response->assertSee('id="hero"', false);
        $response->assertSee('id="awards"', false);
        $response->assertSee('id="contact"', false);
        $response->assertSee('id="newsletter"', false);
    }
}
