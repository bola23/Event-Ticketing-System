<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_testimonial(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.testimonials.store', $event), [
            'quote_ar' => 'حدث رائع', 'quote_en' => 'A great event',
            'name_ar' => 'سارة', 'name_en' => 'Sarah',
            'title_ar' => 'مؤسسة', 'title_en' => 'Founder', 'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.testimonials.index', $event));
        $this->assertDatabaseHas('testimonials', ['event_id' => $event->id, 'quote_en' => 'A great event']);
    }

    public function test_creating_a_testimonial_requires_bilingual_quote(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.testimonials.store', $event), [
            'quote_ar' => '', 'quote_en' => '', 'name_ar' => 'سارة', 'name_en' => 'Sarah', 'title_ar' => 'مؤسسة', 'title_en' => 'Founder',
        ]);

        $response->assertSessionHasErrors(['quote_ar', 'quote_en']);
    }

    public function test_admin_can_delete_a_testimonial(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $testimonial = Testimonial::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.testimonials.destroy', [$event, $testimonial]));

        $response->assertRedirect(route('admin.events.testimonials.index', $event));
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }

    public function test_admin_can_view_the_index_page_with_records(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        Testimonial::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.testimonials.index', $event));

        $response->assertOk();
    }

    public function test_admin_can_view_the_edit_page(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $testimonial = Testimonial::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.testimonials.edit', [$event, $testimonial]));

        $response->assertOk();
    }
}
