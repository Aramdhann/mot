<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_equals_principal_without_payments(): void
    {
        $loan = Loan::create(['name' => 'Motor', 'principal' => 14000000, 'started_on' => now()]);

        $this->assertEquals(14000000, $loan->remaining);
        $this->assertEquals(0, $loan->paid);
    }

    public function test_paid_sums_only_loan_payment_transactions(): void
    {
        $wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        $loan = Loan::create(['name' => 'Motor', 'principal' => 14000000, 'started_on' => now()]);

        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'loan_payment', 'amount' => 1000000, 'loan_id' => $loan->id, 'occurred_on' => now()]);
        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'loan_payment', 'amount' => 500000, 'loan_id' => $loan->id, 'occurred_on' => now()]);
        // same wallet expense, not a loan payment — must not count
        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'expense', 'amount' => 250000, 'category' => 'makan', 'occurred_on' => now()]);

        $this->assertEquals(1500000, $loan->fresh()->paid);
        $this->assertEquals(12500000, $loan->fresh()->remaining);
    }

    public function test_fully_paid_loan_reaches_zero(): void
    {
        $wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        $loan = Loan::create(['name' => 'Paylater', 'principal' => 500000, 'started_on' => now()]);

        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'loan_payment', 'amount' => 500000, 'loan_id' => $loan->id, 'occurred_on' => now()]);

        $this->assertEquals(0, $loan->fresh()->remaining);
    }
}
