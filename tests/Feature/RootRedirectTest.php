<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class RootRedirectTest extends TestCase
{
    public function test_root_redirects_to_ccs_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/events/ccs-2026');
    }
}
