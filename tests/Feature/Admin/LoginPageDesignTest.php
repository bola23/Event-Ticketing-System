<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Tests\TestCase;

class LoginPageDesignTest extends TestCase
{
    public function test_login_page_shows_the_ccs_brand_mark(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('CCS');
        $response->assertSee('ccs-flag-accent', false);
        $response->assertSee('ccs-hero', false);
    }
}
