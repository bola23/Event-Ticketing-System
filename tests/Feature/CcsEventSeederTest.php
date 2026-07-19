<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CcsEventSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_ccs_event_with_related_content(): void
    {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CcsEventSeeder']);

        $event = Event::where('slug', 'ccs-2026')->firstOrFail();

        $this->assertGreaterThanOrEqual(3, $event->speakers()->count());
        $this->assertGreaterThanOrEqual(2, $event->sponsors()->count());
        $this->assertSame(4, $event->ticketTypes()->count());
        $this->assertGreaterThanOrEqual(2, $event->workshops()->count());
        $this->assertGreaterThanOrEqual(1, $event->agendaItems()->count());
        $this->assertGreaterThanOrEqual(3, $event->faqs()->count());
        $this->assertNotNull($event->landingPageContent()->count());
    }
}
