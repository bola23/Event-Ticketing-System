<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeakerTest extends TestCase
{
    use RefreshDatabase;

    public function test_speaker_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $speaker = Speaker::factory()->for($event)->create();

        $this->assertTrue($speaker->event->is($event));
    }

    public function test_event_has_many_speakers(): void
    {
        $event = Event::factory()->create();
        Speaker::factory()->count(3)->for($event)->create();

        $this->assertCount(3, $event->speakers);
    }
}
