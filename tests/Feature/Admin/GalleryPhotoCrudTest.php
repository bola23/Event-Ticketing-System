<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryPhotoCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_a_gallery_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.gallery-photos.store', $event), [
            'image' => UploadedFile::fake()->image('hall.jpg'),
            'caption_ar' => 'القاعة',
            'caption_en' => 'The Hall',
            'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.gallery-photos.index', $event));
        $photo = GalleryPhoto::where('caption_en', 'The Hall')->firstOrFail();
        $this->assertStringStartsWith('gallery/', $photo->image_path);
        Storage::disk('public')->assertExists($photo->image_path);
    }

    public function test_creating_a_gallery_photo_requires_an_image(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.gallery-photos.store', $event), []);

        $response->assertSessionHasErrors(['image']);
    }

    public function test_a_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.gallery-photos.store', $event), [
            'image' => UploadedFile::fake()->create('notes.pdf', 20, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors(['image']);
    }

    public function test_editing_without_choosing_a_file_keeps_the_stored_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $photo = GalleryPhoto::factory()->for($event)->create(['image_path' => 'gallery/original.jpg']);

        $response = $this->actingAs($admin)->put(route('admin.events.gallery-photos.update', [$event, $photo]), [
            'caption_en' => 'Renamed',
            'caption_ar' => 'مُعاد التسمية',
            'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.gallery-photos.index', $event));
        $this->assertSame('gallery/original.jpg', $photo->fresh()->image_path);
        $this->assertSame('Renamed', $photo->fresh()->caption_en);
    }

    public function test_uploading_a_replacement_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        Storage::disk('public')->put('gallery/original.jpg', 'x');
        $photo = GalleryPhoto::factory()->for($event)->create(['image_path' => 'gallery/original.jpg']);

        $this->actingAs($admin)->put(route('admin.events.gallery-photos.update', [$event, $photo]), [
            'image' => UploadedFile::fake()->image('new.jpg'),
            'caption_en' => 'Updated',
            'caption_ar' => 'محدث',
            'sort_order' => 0,
        ]);

        Storage::disk('public')->assertMissing('gallery/original.jpg');
        Storage::disk('public')->assertExists($photo->fresh()->image_path);
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
