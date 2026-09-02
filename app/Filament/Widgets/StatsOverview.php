<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalBalance = array_sum(Wallet::balancesById());

        $month = [now()->startOfMonth(), now()->endOfMonth()];
        $income = (float) Transaction::where('type', TransactionType::Income)->whereBetween('occurred_on', $month)->sum('amount');
        $spending = (float) Transaction::whereIn('type', [TransactionType::Expense, TransactionType::LoanPayment])->whereBetween('occurred_on', $month)->sum('amount');

        return [
            Stat::make('Total balance', 'IDR '.number_format($totalBalance, 0))
                ->color($totalBalance >= 0 ? 'success' : 'danger'),
            Stat::make('Income this month', 'IDR '.number_format($income, 0))
                ->color('success'),
            Stat::make('Spending this month', 'IDR '.number_format($spending, 0))
                ->color('danger'),
        ];
    }
}
