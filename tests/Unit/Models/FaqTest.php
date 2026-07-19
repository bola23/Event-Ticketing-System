<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $faq = Faq::factory()->for($event)->create();

        $this->assertTrue($faq->event->is($event));
    }

    public function test_event_has_many_faqs_ordered_by_sort_order(): void
    {
        $event = Event::factory()->create();
        Faq::factory()->for($event)->create(['sort_order' => 2, 'question_en' => 'Second']);
        Faq::factory()->for($event)->create(['sort_order' => 1, 'question_en' => 'First']);

        $this->assertSame('First', $event->faqs->first()->question_en);
    }
}
