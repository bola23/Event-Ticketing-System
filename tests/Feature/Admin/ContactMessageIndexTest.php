<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_submitted_messages(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        ContactMessage::factory()->for($event)->create(['name' => 'Jane Creator']);

        $response = $this->actingAs($admin)->get(route('admin.events.contact-messages.index', $event));

        $response->assertOk();
        $response->assertSee('Jane Creator');
    }

    public function test_admin_index_shows_empty_state_with_no_messages(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.events.contact-messages.index', $event));

        $response->assertOk();
        $response->assertSee('No messages yet.');
    }
}
