<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Reel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reel>
 */
class ReelFactory extends Factory
{
    protected $model = Reel::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'video_path' => 'reels/'.fake()->uuid().'.mp4',
            'poster_path' => 'reels/posters/'.fake()->uuid().'.jpg',
            'caption_ar' => fake()->words(3, true),
            'caption_en' => fake()->words(3, true),
            'sort_order' => 0,
        ];
    }
}
