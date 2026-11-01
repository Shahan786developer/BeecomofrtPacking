<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    public function test_the_application_returns_a_successful_response(): void
    {
        $admin = \App\Models\User::where('email', 'superadmin@gmail.com')->first();
        $this->actingAs($admin);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }
}
