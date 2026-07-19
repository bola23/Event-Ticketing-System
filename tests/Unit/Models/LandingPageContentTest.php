<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\LandingPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $content = LandingPageContent::factory()->for($event)->create();

        $this->assertTrue($content->event->is($event));
    }

    public function test_event_can_look_up_a_specific_field(): void
    {
        $event = Event::factory()->create();
        LandingPageContent::factory()->for($event)->create([
            'section' => LandingPageSection::Hero,
            'field_key' => 'headline',
            'value_ar' => 'أثر يتوالى',
            'value_en' => 'Impact that continues',
        ]);

        $field = $event->contentFor(LandingPageSection::Hero, 'headline');

        $this->assertNotNull($field);
        $this->assertSame('Impact that continues', $field->value_en);
    }

    public function test_content_for_returns_null_when_missing(): void
    {
        $event = Event::factory()->create();

        $this->assertNull($event->contentFor(LandingPageSection::About, 'body'));
    }
}
