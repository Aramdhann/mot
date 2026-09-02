<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render(): void
    {
        $user = User::factory()->create();

        foreach (['/admin', '/admin/wallets', '/admin/transactions', '/admin/budgets', '/admin/loans'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_transaction_form_renders(): void
    {
        $this->actingAs(User::factory()->create());

        \Livewire\Livewire::test(\App\Filament\Resources\Transactions\Pages\ManageTransactions::class)
            ->mountAction('create')
            ->assertSuccessful();
    }
}
