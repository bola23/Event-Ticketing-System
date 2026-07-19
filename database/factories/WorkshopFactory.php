<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkshopFactory extends Factory
{
    protected $model = Workshop::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'speaker_id' => null,
            'slug' => $this->faker->unique()->slug(3),
            'name_ar' => $this->faker->sentence(3),
            'name_en' => $this->faker->sentence(3),
            'description_ar' => $this->faker->paragraph(),
            'description_en' => $this->faker->paragraph(),
            'capacity' => $this->faker->numberBetween(10, 50),
            'sort_order' => 0,
        ];
    }
}
