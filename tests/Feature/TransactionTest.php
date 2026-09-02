<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_normalizes_category_and_loan(): void
    {
        $a = Wallet::create(['name' => 'A', 'type' => 'cash']);
        $b = Wallet::create(['name' => 'B', 'type' => 'cash']);

        $t = Transaction::create([
            'wallet_id' => $a->id,
            'transfer_to_wallet_id' => $b->id,
            'type' => 'transfer',
            'amount' => 100000,
            'category' => 'should-be-nulled',
            'occurred_on' => now(),
        ]);

        $this->assertNull($t->fresh()->category);
        $this->assertNull($t->fresh()->loan_id);
        $this->assertEquals($b->id, $t->fresh()->transfer_to_wallet_id);
    }

    public function test_loan_payment_forces_category_and_clears_transfer(): void
    {
        $wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        $loan = Loan::create(['name' => 'Motor', 'principal' => 14000000, 'started_on' => now()]);

        $t = Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'loan_payment',
            'amount' => 1000000,
            'loan_id' => $loan->id,
            'category' => 'ignored',
            'occurred_on' => now(),
        ]);

        $this->assertEquals('loan', $t->fresh()->category);
        $this->assertNull($t->fresh()->transfer_to_wallet_id);
        $this->assertEquals($loan->id, $t->fresh()->loan_id);
    }

    public function test_income_and_expense_clear_transfer_and_loan_fields(): void
    {
        $wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);

        $t = Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 50000,
            'category' => 'makan',
            'transfer_to_wallet_id' => $wallet->id,
            'occurred_on' => now(),
        ]);

        $this->assertNull($t->fresh()->transfer_to_wallet_id);
        $this->assertNull($t->fresh()->loan_id);
        $this->assertEquals('makan', $t->fresh()->category);
    }

    public function test_transfer_to_same_wallet_is_rejected_by_filament_form(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);

        \Livewire\Livewire::test(\App\Filament\Resources\Transactions\Pages\ManageTransactions::class)
            ->callAction('create', data: [
                'type' => TransactionType::Transfer->value,
                'wallet_id' => $wallet->id,
                'transfer_to_wallet_id' => $wallet->id,
                'amount' => 100000,
                'occurred_on' => now()->format('Y-m-d'),
            ])
            ->assertHasActionErrors(['transfer_to_wallet_id']);

        $this->assertDatabaseCount('transactions', 0);
    }
}
