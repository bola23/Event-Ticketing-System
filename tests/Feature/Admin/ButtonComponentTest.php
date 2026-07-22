<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ButtonComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_button_renders_as_link_when_href_given(): void
    {
        $html = Blade::render('<x-admin.button href="/somewhere">Go</x-admin.button>');

        $this->assertStringContainsString('<a href="/somewhere"', $html);
        $this->assertStringContainsString('Go', $html);
    }

    public function test_button_renders_as_submit_button_by_default(): void
    {
        $html = Blade::render('<x-admin.button>Save</x-admin.button>');

        $this->assertStringContainsString('<button type="submit"', $html);
        $this->assertStringContainsString('Save', $html);
    }
}
