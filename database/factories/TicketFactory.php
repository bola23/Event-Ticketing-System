<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'ticket_type_id' => TicketType::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => '+201001234567',
            'ticket_number' => strtoupper($this->faker->bothify('TKT-######')),
            'status' => 'pending',
            'is_paid' => false,
        ];
    }
}
