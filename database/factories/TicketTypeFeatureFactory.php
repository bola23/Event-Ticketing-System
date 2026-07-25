<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TicketType;
use App\Models\TicketTypeFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTypeFeatureFactory extends Factory
{
    protected $model = TicketTypeFeature::class;

    public function definition(): array
    {
        return [
            'ticket_type_id' => TicketType::factory(),
            'text_ar' => $this->faker->sentence(3),
            'text_en' => $this->faker->sentence(3),
            'sort_order' => 0,
        ];
    }
}
