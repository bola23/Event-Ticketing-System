<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageHeaderComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_title_and_flag_accent(): void
    {
        $html = Blade::render('<x-admin.page-header title="Speakers" />');

        $this->assertStringContainsString('Speakers', $html);
        $this->assertStringContainsString('ccs-flag-accent', $html);
    }

    public function test_renders_optional_action_slot(): void
    {
        $html = Blade::render('<x-admin.page-header title="Speakers"><a href="/new">New</a></x-admin.page-header>');

        $this->assertStringContainsString('<a href="/new">New</a>', $html);
    }

    public function test_no_empty_action_wrapper_when_no_slot_given(): void
    {
        $html = Blade::render('<x-admin.page-header title="Speakers" />');

        $this->assertStringNotContainsString('<div></div>', $html);
    }
}
