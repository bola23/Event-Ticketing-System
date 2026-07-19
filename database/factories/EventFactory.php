<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(2),
            'name_ar' => $this->faker->sentence(3),
            'name_en' => $this->faker->sentence(3),
            'tagline_ar' => $this->faker->sentence(4),
            'tagline_en' => $this->faker->sentence(4),
            'start_date' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+3 months'),
            'venue_name_ar' => $this->faker->company(),
            'venue_name_en' => $this->faker->company(),
            'venue_address_ar' => $this->faker->address(),
            'venue_address_en' => $this->faker->address(),
            'map_embed_url' => $this->faker->url(),
            'status' => EventStatus::Draft,
        ];
    }
}
