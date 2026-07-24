<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_submit_the_contact_form(): void
    {
        $event = Event::factory()->create();

        $response = $this->post(route('contact.store', $event), [
            'name' => 'Jane Creator', 'email' => 'jane@example.com', 'message' => 'What time do doors open?',
        ]);

        $response->assertRedirect(route('landing.show', $event).'#contact');
        $this->assertDatabaseHas('contact_messages', [
            'event_id' => $event->id, 'name' => 'Jane Creator', 'email' => 'jane@example.com',
        ]);
    }

    public function test_contact_form_requires_a_valid_email(): void
    {
        $event = Event::factory()->create();

        $response = $this->post(route('contact.store', $event), [
            'name' => 'Jane Creator', 'email' => 'not-an-email', 'message' => 'Hello',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_section_renders_the_form(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('id="contact"', false);
        $response->assertSee('action="'.route('contact.store', $event).'"', false);
    }
}
