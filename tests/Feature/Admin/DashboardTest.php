<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_event_counts(): void
    {
        $admin = User::factory()->create();
        Event::factory()->create(['status' => EventStatus::Published]);
        Event::factory()->create(['status' => EventStatus::Published]);
        Event::factory()->create(['status' => EventStatus::Draft]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('3');
        $response->assertSee('2');
        $response->assertSee('1');
    }
}
