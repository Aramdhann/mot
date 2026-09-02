<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }

    public function test_custom_404_renders(): void
    {
        $this->get('/nonexistent-page')
            ->assertStatus(404)
            ->assertSee('404')
            ->assertSee('Back to dashboard');
    }
}
