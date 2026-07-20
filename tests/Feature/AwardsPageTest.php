<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_awards_placeholder_page_renders(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('awards.show', $event));

        $response->assertStatus(200);
        $response->assertSee(__('Awards'));
    }
}
