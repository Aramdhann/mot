<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_renders_when_enabled(): void
    {
        $this->get('/admin/register')->assertOk();
    }

    public function test_user_can_register_and_is_logged_in(): void
    {
        \Livewire\Livewire::test(\App\Filament\Auth\Register::class)
            ->fillForm([
                'name' => 'New User',
                'email' => 'new@mot.local',
                'password' => 'secret-password',
                'passwordConfirmation' => 'secret-password',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['email' => 'new@mot.local', 'name' => 'New User']);
        $this->assertAuthenticatedAs(User::where('email', 'new@mot.local')->first());
    }

    public function test_registration_is_forbidden_when_disabled(): void
    {
        config(['app.allow_registration' => false]);

        $this->get('/admin/register')->assertForbidden();
    }
}
