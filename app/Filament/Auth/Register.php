<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Register as BaseRegister;

/**
 * Register page gated by ALLOW_REGISTRATION.
 * The panel provider also removes the route entirely when the env var is false at boot;
 * this runtime check is the second layer (and covers config flipped after boot).
 */
class Register extends BaseRegister
{
    public function mount(): void
    {
        abort_unless(config('app.allow_registration'), 403);

        parent::mount();
    }
}
