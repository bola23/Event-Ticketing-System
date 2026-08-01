<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketRequestAnswer;
use App\Models\TicketRequestField;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketRequestAnswerFactory extends Factory
{
    protected $model = TicketRequestAnswer::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'ticket_request_field_id' => TicketRequestField::factory(),
            'value' => '@'.$this->faker->userName(),
            'file_path' => null,
        ];
    }
}
