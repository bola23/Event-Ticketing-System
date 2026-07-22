<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BilingualFieldComponentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Blade::render() bypasses the `web` middleware group, so the
        // ShareErrorsFromSession middleware never shares $errors with the
        // view. Share an empty error bag so the component's @error
        // directive has something to inspect, matching real request behavior.
        $this->withViewErrors([]);
    }

    public function test_renders_both_ar_and_en_inputs(): void
    {
        $html = Blade::render(
            '<x-admin.bilingual-field name="name" label="Name" :value-ar="$ar" :value-en="$en" />',
            ['ar' => 'اسم', 'en' => 'Name Value']
        );

        $this->assertStringContainsString('name="name_ar"', $html);
        $this->assertStringContainsString('name="name_en"', $html);
        $this->assertStringContainsString('value="اسم"', $html);
        $this->assertStringContainsString('value="Name Value"', $html);
        $this->assertStringContainsString('dir="rtl"', $html);
        $this->assertStringContainsString('Name (Arabic)', $html);
        $this->assertStringContainsString('Name (English)', $html);
    }
}
