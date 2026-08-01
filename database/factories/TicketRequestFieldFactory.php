<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketRequestField;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketRequestFieldFactory extends Factory
{
    protected $model = TicketRequestField::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'type' => 'instagram',
            'label_ar' => 'إنستغرام',
            'label_en' => 'Instagram',
            'is_required' => false,
            'sort_order' => 0,
        ];
    }
}
