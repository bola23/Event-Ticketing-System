<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsletterSubscriberFactory extends Factory
{
    protected $model = NewsletterSubscriber::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
