<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\SponsorRequestStatus;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SponsorRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(): array
    {
        return [
            'name_en' => 'Acme Interiors',
            'name_ar' => 'أكمي',
            'contact_name' => 'Jane Creator',
            'email' => 'jane@example.com',
            'phone' => '+201001234567',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'website_url' => 'https://acme.example.com',
            'instagram_url' => 'https://instagram.com/acme',
            'facebook_url' => 'https://facebook.com/acme',
            'message' => 'We would like to sponsor the next event.',
        ];
    }

    public function test_the_landing_page_includes_the_sponsor_request_form(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertOk();
        $response->assertSee('action="'.route('sponsor-requests.store', $event).'"', false);
    }

    public function test_visitor_can_submit_a_sponsor_request(): void
    {
        Storage::fake('public');
        $event = Event::factory()->create(['status' => EventStatus::Published]);

        $response = $this->post(route('sponsor-requests.store', $event), $this->validPayload());

        $response->assertRedirect(route('landing.show', $event).'#partners');
        $this->assertDatabaseHas('sponsor_requests', [
            'event_id' => $event->id,
            'name_en' => 'Acme Interiors',
            'name_ar' => 'أكمي',
            'contact_name' => 'Jane Creator',
            'email' => 'jane@example.com',
            'status' => SponsorRequestStatus::Pending->value,
        ]);
        $sponsorRequest = $event->sponsorRequests()->first();
        Storage::disk('public')->assertExists($sponsorRequest->logo_path);
    }

    public function test_website_instagram_facebook_and_message_are_optional(): void
    {
        Storage::fake('public');
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $payload = $this->validPayload();
        unset($payload['website_url'], $payload['instagram_url'], $payload['facebook_url'], $payload['message']);

        $response = $this->post(route('sponsor-requests.store', $event), $payload);

        $response->assertRedirect(route('landing.show', $event).'#partners');
        $this->assertDatabaseHas('sponsor_requests', [
            'event_id' => $event->id, 'website_url' => null, 'instagram_url' => null, 'facebook_url' => null, 'message' => null,
        ]);
    }

    public function test_bilingual_name_is_required(): void
    {
        Storage::fake('public');
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $payload = $this->validPayload();
        $payload['name_en'] = '';
        $payload['name_ar'] = '';

        $response = $this->post(route('sponsor-requests.store', $event), $payload);

        $response->assertSessionHasErrors(['name_en', 'name_ar']);
        $this->assertDatabaseCount('sponsor_requests', 0);
    }

    public function test_logo_is_required(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $payload = $this->validPayload();
        unset($payload['logo']);

        $response = $this->post(route('sponsor-requests.store', $event), $payload);

        $response->assertSessionHasErrors('logo');
        $this->assertDatabaseCount('sponsor_requests', 0);
    }

    public function test_email_must_be_valid(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $payload = $this->validPayload();
        $payload['email'] = 'not-an-email';

        $response = $this->post(route('sponsor-requests.store', $event), $payload);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('sponsor_requests', 0);
    }

    public function test_phone_must_be_valid(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $payload = $this->validPayload();
        $payload['phone'] = 'not-a-phone';

        $response = $this->post(route('sponsor-requests.store', $event), $payload);

        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseCount('sponsor_requests', 0);
    }

    public function test_website_url_must_be_a_valid_url(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $payload = $this->validPayload();
        $payload['website_url'] = 'not-a-url';

        $response = $this->post(route('sponsor-requests.store', $event), $payload);

        $response->assertSessionHasErrors('website_url');
        $this->assertDatabaseCount('sponsor_requests', 0);
    }

    public function test_draft_event_returns_404_on_submit(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Draft]);

        $response = $this->post(route('sponsor-requests.store', $event), $this->validPayload());

        $response->assertStatus(404);
    }

    public function test_general_footer_no_longer_links_to_a_sponsor_form(): void
    {
        $response = $this->get(route('home').'?lang=en');

        $response->assertOk();
        $response->assertDontSee('Become a Sponsor');
    }
}
