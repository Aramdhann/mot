<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PwaTest extends TestCase
{
    public function test_pwa_assets_serve_and_head_hook_is_wired(): void
    {
        $this->get('/manifest.webmanifest')->assertOk()->assertJsonPath('short_name', 'MOT');
        $this->get('/sw.js')->assertOk();

        $this->actingAs(User::factory()->create());

        $this->get('/admin')
            ->assertOk()
            ->assertSee('/manifest.webmanifest', false)
            ->assertSee('apple-touch-icon', false)
            ->assertSee('theme-color', false)
            ->assertSee('serviceWorker', false);
    }
}
