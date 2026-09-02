<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_edit_delete_wallet_through_filament(): void
    {
        $this->actingAs(User::factory()->create());

        \Livewire\Livewire::test(\App\Filament\Resources\Wallets\Pages\ManageWallets::class)
            ->callAction('create', data: ['name' => 'BCA', 'type' => 'bank'])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('wallets', ['name' => 'BCA', 'type' => 'bank']);

        $wallet = Wallet::first();

        \Livewire\Livewire::test(\App\Filament\Resources\Wallets\Pages\ManageWallets::class)
            ->callTableAction('edit', $wallet, data: ['name' => 'BCA Utama'])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('wallets', ['name' => 'BCA Utama']);

        \Livewire\Livewire::test(\App\Filament\Resources\Wallets\Pages\ManageWallets::class)
            ->callTableAction('delete', $wallet);

        $this->assertDatabaseMissing('wallets', ['name' => 'BCA Utama']);
    }

    public function test_create_income_transaction_through_filament(): void
    {
        $this->actingAs(User::factory()->create());
        $wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);

        \Livewire\Livewire::test(\App\Filament\Resources\Transactions\Pages\ManageTransactions::class)
            ->callAction('create', data: [
                'type' => 'income',
                'wallet_id' => $wallet->id,
                'amount' => 2500000,
                'category' => 'gaji',
                'description' => 'Salary',
                'occurred_on' => now()->format('Y-m-d'),
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('transactions', ['amount' => 2500000, 'category' => 'gaji']);
        $this->assertEquals(2500000, $wallet->fresh()->balance);
    }

    public function test_create_transfer_through_filament(): void
    {
        $this->actingAs(User::factory()->create());
        $bca = Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        $sea = Wallet::create(['name' => 'SeaBank', 'type' => 'bank']);

        \Livewire\Livewire::test(\App\Filament\Resources\Transactions\Pages\ManageTransactions::class)
            ->callAction('create', data: [
                'type' => 'transfer',
                'wallet_id' => $bca->id,
                'transfer_to_wallet_id' => $sea->id,
                'amount' => 300000,
                'occurred_on' => now()->format('Y-m-d'),
            ])
            ->assertHasNoActionErrors();

        $this->assertEquals(-300000, $bca->fresh()->balance);
        $this->assertEquals(300000, $sea->fresh()->balance);
    }

    public function test_create_loan_payment_through_filament_updates_loan(): void
    {
        $this->actingAs(User::factory()->create());
        $wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        $loan = \App\Models\Loan::create(['name' => 'Motor', 'principal' => 14000000, 'started_on' => now()]);

        \Livewire\Livewire::test(\App\Filament\Resources\Transactions\Pages\ManageTransactions::class)
            ->callAction('create', data: [
                'type' => 'loan_payment',
                'wallet_id' => $wallet->id,
                'loan_id' => $loan->id,
                'amount' => 1000000,
                'occurred_on' => now()->format('Y-m-d'),
            ])
            ->assertHasNoActionErrors();

        $this->assertEquals(13000000, $loan->fresh()->remaining);
    }

    public function test_create_budget_and_loan_through_filament(): void
    {
        $this->actingAs(User::factory()->create());

        \Livewire\Livewire::test(\App\Filament\Resources\Budgets\Pages\ManageBudgets::class)
            ->callAction('create', data: ['category' => 'makan', 'amount' => 2000000])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('budgets', ['category' => 'makan', 'amount' => 2000000]);

        \Livewire\Livewire::test(\App\Filament\Resources\Loans\Pages\ManageLoans::class)
            ->callAction('create', data: [
                'name' => 'Motor',
                'principal' => 14000000,
                'started_on' => now()->format('Y-m-d'),
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('loans', ['name' => 'Motor']);
    }
}
