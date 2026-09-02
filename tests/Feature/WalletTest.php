<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_balance_sums_income_and_expense(): void
    {
        $wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'income', 'amount' => 2500000, 'category' => 'gaji', 'occurred_on' => now()]);
        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'expense', 'amount' => 75000, 'category' => 'makan', 'occurred_on' => now()]);

        $this->assertEquals(2425000, $wallet->balance);
    }

    public function test_transfer_moves_money_between_wallets(): void
    {
        $bca = Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        $sea = Wallet::create(['name' => 'SeaBank', 'type' => 'bank']);
        Transaction::create(['wallet_id' => $bca->id, 'type' => 'income', 'amount' => 1000000, 'category' => 'gaji', 'occurred_on' => now()]);
        Transaction::create(['wallet_id' => $bca->id, 'transfer_to_wallet_id' => $sea->id, 'type' => 'transfer', 'amount' => 300000, 'occurred_on' => now()]);

        $this->assertEquals(700000, $bca->fresh()->balance);
        $this->assertEquals(300000, $sea->fresh()->balance);
    }

    public function test_loan_payment_reduces_wallet_balance(): void
    {
        $wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        $loan = Loan::create(['name' => 'Motor', 'principal' => 14000000, 'started_on' => now()]);
        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'income', 'amount' => 2000000, 'category' => 'gaji', 'occurred_on' => now()]);
        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'loan_payment', 'amount' => 500000, 'loan_id' => $loan->id, 'occurred_on' => now()]);

        $this->assertEquals(1500000, $wallet->balance);
    }

    public function test_wallet_without_transactions_has_zero_balance(): void
    {
        $wallet = Wallet::create(['name' => 'Cash', 'type' => 'cash']);

        $this->assertEquals(0, $wallet->balance);
    }

    public function test_deleting_wallet_cascades_its_transactions(): void
    {
        $wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'expense', 'amount' => 10000, 'category' => 'makan', 'occurred_on' => now()]);

        $wallet->delete();

        $this->assertDatabaseCount('transactions', 0);
    }
}
