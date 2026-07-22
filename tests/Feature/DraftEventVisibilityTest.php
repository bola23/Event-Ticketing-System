<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DraftEventVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_event_landing_page_returns_404(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Draft]);

        $response = $this->get(route('landing.show', $event));

        $response->assertStatus(404);
    }

    public function test_published_event_landing_page_returns_200(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);

        $response = $this->get(route('landing.show', $event));

        $response->assertStatus(200);
    }

    public function test_draft_event_agenda_page_returns_404(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Draft]);

        $response = $this->get(route('agenda.show', $event));

        $response->assertStatus(404);
    }

    public function test_draft_event_awards_page_returns_404(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Draft]);

        $response = $this->get(route('awards.show', $event));

        $response->assertStatus(404);
    }

    public function test_draft_event_ticket_request_page_returns_404(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Draft]);

        $response = $this->get(route('ticket-requests.create', $event));

        $response->assertStatus(404);
    }

    public function test_draft_event_workshops_index_returns_404(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Draft]);

        $response = $this->get(route('workshops.index', $event));

        $response->assertStatus(404);
    }

    public function test_draft_event_workshop_show_returns_404(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Draft]);
        $workshop = Workshop::factory()->for($event)->create();

        $response = $this->get(route('workshops.show', [$event, $workshop]));

        $response->assertStatus(404);
    }
}
