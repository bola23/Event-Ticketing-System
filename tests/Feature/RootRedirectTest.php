<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class RootRedirectTest extends TestCase
{
    public function test_root_renders_the_creators_hub_home_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Creators', false);
    }
}
