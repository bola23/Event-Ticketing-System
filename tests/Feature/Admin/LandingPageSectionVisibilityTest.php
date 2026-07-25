<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageSectionVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function payloadWithVisibleSections(array $visibleSections): array
    {
        return [
            'hero_headline_ar' => 'العنوان', 'hero_headline_en' => 'Headline',
            'about_body_ar' => 'نبذة', 'about_body_en' => 'About body',
            'location_intro_ar' => 'الموقع', 'location_intro_en' => 'Location intro',
            'awards_teaser_blurb_ar' => 'الجوائز', 'awards_teaser_blurb_en' => 'Awards blurb',
            'stats_attendees_count_ar' => '٢٠٠+', 'stats_attendees_count_en' => '200+',
            'stats_countries_count_ar' => '١٥', 'stats_countries_count_en' => '15',
            'visible_sections' => $visibleSections,
        ];
    }

    public function test_admin_can_hide_a_section(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        Sponsor::factory()->for($event)->create();

        $allExceptPartners = array_values(array_diff(Event::TOGGLEABLE_SECTIONS, ['partners']));

        $response = $this->actingAs($admin)->put(
            route('admin.events.content.update', $event),
            $this->payloadWithVisibleSections($allExceptPartners),
        );

        $response->assertRedirect(route('admin.events.content.edit', $event));
        $event->refresh();
        $this->assertFalse($event->isSectionVisible('partners'));
        $this->assertTrue($event->isSectionVisible('about'));
    }

    public function test_hidden_section_does_not_render_on_public_page_even_with_data(): void
    {
        $event = Event::factory()->create([
            'visible_sections' => array_merge(array_fill_keys(Event::TOGGLEABLE_SECTIONS, true), ['partners' => false]),
        ]);
        Sponsor::factory()->for($event)->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertOk();
        $response->assertDontSee('id="partners"', false);
    }

    public function test_section_with_no_stored_preference_defaults_to_visible(): void
    {
        $event = Event::factory()->create();
        Sponsor::factory()->for($event)->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertOk();
        $response->assertSee('id="partners"', false);
    }
}
