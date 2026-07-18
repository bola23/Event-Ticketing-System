<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_application_boots(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
