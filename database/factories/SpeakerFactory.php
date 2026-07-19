<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpeakerFactory extends Factory
{
    protected $model = Speaker::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name_ar' => $this->faker->name(),
            'name_en' => $this->faker->name(),
            'title_ar' => $this->faker->jobTitle(),
            'title_en' => $this->faker->jobTitle(),
            'bio_ar' => $this->faker->paragraph(),
            'bio_en' => $this->faker->paragraph(),
            'photo_path' => null,
            'sort_order' => 0,
        ];
    }
}
