<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    private function spend(float $amount, string $category, $date = null): void
    {
        $wallet = Wallet::first() ?? Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => $amount,
            'category' => $category,
            'occurred_on' => $date ?? now(),
        ]);
    }

    public function test_spent_counts_matching_category_this_month(): void
    {
        $budget = Budget::create(['category' => 'makan', 'amount' => 2000000]);
        $this->spend(75000, 'makan');
        $this->spend(15000, 'makan');

        $this->assertEquals(90000, $budget->fresh()->spent_this_month);
    }

    public function test_spent_ignores_other_months(): void
    {
        $budget = Budget::create(['category' => 'makan', 'amount' => 2000000]);
        $this->spend(500000, 'makan', now()->subMonth());

        $this->assertEquals(0, $budget->fresh()->spent_this_month);
    }

    public function test_spent_ignores_other_categories(): void
    {
        $budget = Budget::create(['category' => 'makan', 'amount' => 2000000]);
        $this->spend(100000, 'transport');

        $this->assertEquals(0, $budget->fresh()->spent_this_month);
    }

    public function test_spent_ignores_income_with_same_category(): void
    {
        $budget = Budget::create(['category' => 'makan', 'amount' => 2000000]);
        $wallet = Wallet::first() ?? Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'income', 'amount' => 300000, 'category' => 'makan', 'occurred_on' => now()]);

        $this->assertEquals(0, $budget->fresh()->spent_this_month);
    }
}
