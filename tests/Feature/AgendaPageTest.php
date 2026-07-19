<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_page_lists_items_grouped_by_day(): void
    {
        $event = Event::factory()->create();
        AgendaItem::factory()->for($event)->create([
            'title_en' => 'Opening Keynote',
            'day_date' => '2026-08-15',
        ]);

        $response = $this->get(route('agenda.show', $event).'?lang=en');

        $response->assertStatus(200);
        $response->assertSee('Opening Keynote');
    }
}
