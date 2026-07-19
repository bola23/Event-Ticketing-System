<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AgendaItemType;
use App\Models\AgendaItem;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgendaItemFactory extends Factory
{
    protected $model = AgendaItem::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'speaker_id' => null,
            'workshop_id' => null,
            'day_date' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'title_ar' => $this->faker->sentence(3),
            'title_en' => $this->faker->sentence(3),
            'type' => AgendaItemType::Session,
            'sort_order' => 0,
        ];
    }
}
