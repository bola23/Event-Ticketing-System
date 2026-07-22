<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_wraps_slot_content(): void
    {
        $html = Blade::render('<x-admin.table><tbody><tr><td>Row</td></tr></tbody></x-admin.table>');

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<tr><td>Row</td></tr>', $html);
    }

    public function test_empty_state_renders_message(): void
    {
        $html = Blade::render('<x-admin.empty-state message="No speakers yet." />');

        $this->assertStringContainsString('No speakers yet.', $html);
    }
}
