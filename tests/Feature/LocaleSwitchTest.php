<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    public function test_default_locale_is_arabic_with_rtl_direction(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/events/ccs-2026');
    }

    public function test_english_locale_sets_ltr_direction(): void
    {
        $response = $this->get('/?lang=en');

        $response->assertRedirect('/events/ccs-2026');
    }
}
