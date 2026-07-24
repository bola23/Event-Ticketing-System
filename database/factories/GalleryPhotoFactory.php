<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\GalleryPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

class GalleryPhotoFactory extends Factory
{
    protected $model = GalleryPhoto::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'image_path' => '/images/placeholder-gallery.jpg',
            'caption_ar' => $this->faker->sentence(3),
            'caption_en' => $this->faker->sentence(3),
            'sort_order' => 0,
        ];
    }
}
