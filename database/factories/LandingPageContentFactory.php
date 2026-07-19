<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\LandingPageContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class LandingPageContentFactory extends Factory
{
    protected $model = LandingPageContent::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'section' => LandingPageSection::About,
            'field_key' => 'body',
            'value_ar' => $this->faker->paragraph(),
            'value_en' => $this->faker->paragraph(),
        ];
    }
}
