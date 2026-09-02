<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use BelongsToUser;

    protected $fillable = ['category', 'amount'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Total budgeted limits vs this month's income — budgets with no limit are excluded
     * (they don't cap spending, so they can't overshoot income).
     *
     * @return array{budgeted: float, income: float}
     */
    public static function totalsVsIncome(): array
    {
        return [
            'budgeted' => (float) static::whereNotNull('amount')->sum('amount'),
            'income' => (float) Transaction::where('type', TransactionType::Income)
                ->whereBetween('occurred_on', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
        ];
    }

    public function getSpentThisMonthAttribute(): float
    {
        // ponytail: per-budget query, N+1 on the list page; fine for ~dozens of budgets
        return (float) Transaction::query()
            ->where('type', 'expense')
            ->where('category', $this->category)
            ->whereBetween('occurred_on', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
    }
}
