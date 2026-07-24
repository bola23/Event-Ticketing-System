<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryPhotoCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_gallery_photo(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.gallery-photos.store', $event), [
            'image_path' => '/images/hall.jpg', 'caption_ar' => 'القاعة', 'caption_en' => 'The Hall', 'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.gallery-photos.index', $event));
        $this->assertDatabaseHas('gallery_photos', ['event_id' => $event->id, 'caption_en' => 'The Hall']);
    }

    public function test_creating_a_gallery_photo_requires_an_image_path(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.gallery-photos.store', $event), [
            'image_path' => '',
        ]);

        $response->assertSessionHasErrors(['image_path']);
    }

    public function test_admin_can_delete_a_gallery_photo(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $photo = GalleryPhoto::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.gallery-photos.destroy', [$event, $photo]));

        $response->assertRedirect(route('admin.events.gallery-photos.index', $event));
        $this->assertDatabaseMissing('gallery_photos', ['id' => $photo->id]);
    }

    public function test_admin_can_view_the_index_page_with_records(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        GalleryPhoto::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.gallery-photos.index', $event));

        $response->assertOk();
    }

    public function test_admin_can_view_the_edit_page(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $photo = GalleryPhoto::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.gallery-photos.edit', [$event, $photo]));

        $response->assertOk();
    }
}
