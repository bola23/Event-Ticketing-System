<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Sponsor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorTest extends TestCase
{
    use RefreshDatabase;

    public function test_sponsor_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $sponsor = Sponsor::factory()->for($event)->create();

        $this->assertTrue($sponsor->event->is($event));
    }

    public function test_event_has_many_sponsors(): void
    {
        $event = Event::factory()->create();
        Sponsor::factory()->count(2)->for($event)->create(['tier' => 'gold']);

        $this->assertCount(2, $event->sponsors);
        $this->assertSame('gold', $event->sponsors->first()->tier);
    }
}
