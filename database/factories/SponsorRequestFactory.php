<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SponsorRequestStatus;
use App\Models\Event;
use App\Models\SponsorRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class SponsorRequestFactory extends Factory
{
    protected $model = SponsorRequest::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name_ar' => $this->faker->company(),
            'name_en' => $this->faker->company(),
            'contact_name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => '+2010'.$this->faker->numerify('#######'),
            'website_url' => $this->faker->url(),
            'instagram_url' => 'https://instagram.com/'.$this->faker->userName(),
            'facebook_url' => 'https://facebook.com/'.$this->faker->userName(),
            'message' => $this->faker->paragraph(),
            'status' => SponsorRequestStatus::Pending,
        ];
    }
}
