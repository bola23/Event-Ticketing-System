<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'question_ar' => $this->faker->sentence().'؟',
            'question_en' => $this->faker->sentence().'?',
            'answer_ar' => $this->faker->paragraph(),
            'answer_en' => $this->faker->paragraph(),
            'sort_order' => 0,
        ];
    }
}
