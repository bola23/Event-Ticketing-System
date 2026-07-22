<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_sidebar_has_no_event_scoped_links(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertDontSee(__('Speakers'));
    }

    public function test_speakers_index_sidebar_shows_event_scoped_links(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.events.speakers.index', $event));

        $response->assertSee(__('Speakers'));
        $response->assertSee(__('Ticket Types'));
        $response->assertSee($event->name_en);
    }
}
