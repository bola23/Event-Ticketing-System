<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class NiceSelectComponentTest extends TestCase
{
    public function test_renders_hidden_input_with_name_and_options(): void
    {
        $html = view('components.nice-select', [
            'name' => 'ticket_type_id',
            'model' => '$store.ticketRequest.ticketTypeId',
            'options' => [
                ['value' => '1', 'label' => 'General — 300 EGP'],
                ['value' => '2', 'label' => 'VIP — 1200 EGP'],
            ],
        ])->render();

        $this->assertStringContainsString('name="ticket_type_id"', $html);
        $this->assertStringContainsString('type="hidden"', $html);
        $this->assertStringContainsString('General — 300 EGP', $html);
        $this->assertStringContainsString('VIP — 1200 EGP', $html);
        $this->assertStringContainsString('$store.ticketRequest.ticketTypeId', $html);
    }

    public function test_renders_id_attribute_when_given(): void
    {
        $html = view('components.nice-select', [
            'id' => 'ticket_type_id',
            'name' => 'ticket_type_id',
            'model' => '$store.ticketRequest.ticketTypeId',
            'options' => [['value' => '1', 'label' => 'General']],
        ])->render();

        $this->assertStringContainsString('id="ticket_type_id"', $html);
    }
}
