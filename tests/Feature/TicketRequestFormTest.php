<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\TicketRequestField;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketRequestFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_shows_fixed_name_email_phone_fields_when_tickets_exist(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        TicketType::factory()->for($event)->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertOk();
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('id="phone"', false);
    }

    public function test_landing_page_renders_configured_dynamic_fields_in_order(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        TicketType::factory()->for($event)->create();
        $portfolio = TicketRequestField::factory()->for($event)->create(['type' => 'portfolio', 'label_en' => 'Portfolio', 'sort_order' => 1]);
        $instagram = TicketRequestField::factory()->for($event)->create(['type' => 'instagram', 'label_en' => 'Instagram', 'sort_order' => 0]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertOk();
        $response->assertSeeInOrder(['Instagram', 'Portfolio']);
        $response->assertSee('name="field_'.$instagram->id.'"', false);
        $response->assertSee('name="field_'.$portfolio->id.'_mode"', false);
        $response->assertSee('name="field_'.$portfolio->id.'_url"', false);
        $response->assertSee('name="field_'.$portfolio->id.'_file"', false);
    }

    public function test_landing_page_renders_cv_field_as_dropzone(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        TicketType::factory()->for($event)->create();
        $cv = TicketRequestField::factory()->for($event)->create(['type' => 'cv', 'label_en' => 'CV']);

        $response = $this->get(route('landing.show', $event));

        $response->assertSee('name="field_'.$cv->id.'"', false);
        $response->assertSee('type="file"', false);
    }

    public function test_landing_page_omits_dynamic_field_markup_when_event_has_none(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        TicketType::factory()->for($event)->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertOk();
        $response->assertDontSee('field_', false);
    }

    public function test_request_modal_is_omitted_when_event_has_no_active_ticket_types(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);

        $response = $this->get(route('landing.show', $event));

        $response->assertOk();
        $response->assertDontSee('id="ticket_type_id"', false);
    }
}
