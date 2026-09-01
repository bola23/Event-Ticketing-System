<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\GalleryPhoto;
use Tests\TestCase;

class StoredMediaUrlTest extends TestCase
{
    public function test_an_uploaded_path_resolves_to_a_root_relative_url(): void
    {
        $photo = new GalleryPhoto(['image_path' => 'gallery/shot.jpg']);

        // Root-relative, so the link works regardless of the host the site is browsed on.
        $this->assertSame('/storage/gallery/shot.jpg', $photo->imageUrl());
    }

    public function test_the_url_does_not_depend_on_the_configured_app_url(): void
    {
        config(['app.url' => 'https://ccs.example.com']);
        config(['filesystems.disks.public.url' => 'https://ccs.example.com/storage']);

        $photo = new GalleryPhoto(['image_path' => 'gallery/shot.jpg']);

        $this->assertSame('/storage/gallery/shot.jpg', $photo->imageUrl());
    }

    public function test_a_disk_pointed_at_another_host_keeps_its_absolute_url(): void
    {
        config(['app.url' => 'https://ccs.example.com']);
        config(['filesystems.disks.public.url' => 'https://cdn.example.net/media']);

        $photo = new GalleryPhoto(['image_path' => 'gallery/shot.jpg']);

        $this->assertSame('https://cdn.example.net/media/gallery/shot.jpg', $photo->imageUrl());
    }

    public function test_an_external_url_column_passes_through_untouched(): void
    {
        $photo = new GalleryPhoto(['image_path' => 'https://picsum.photos/seed/x/800/800']);

        $this->assertSame('https://picsum.photos/seed/x/800/800', $photo->imageUrl());
    }

    public function test_an_empty_column_resolves_to_null(): void
    {
        $this->assertNull((new GalleryPhoto(['image_path' => null]))->imageUrl());
    }
}
