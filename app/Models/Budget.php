<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['category', 'amount'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
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
