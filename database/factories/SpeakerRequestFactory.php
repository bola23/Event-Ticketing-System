<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SpeakerRequestStatus;
use App\Models\Event;
use App\Models\SpeakerRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpeakerRequestFactory extends Factory
{
    protected $model = SpeakerRequest::class;

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
            'email' => $this->faker->safeEmail(),
            'phone' => '+2010'.$this->faker->numerify('#######'),
            'message' => $this->faker->paragraph(),
            'status' => SpeakerRequestStatus::Pending,
        ];
    }
}
