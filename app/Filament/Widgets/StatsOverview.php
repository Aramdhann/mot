<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $income = (float) Transaction::where('amount', '>', 0)->sum('amount');
        $expense = (float) Transaction::where('amount', '<', 0)->sum('amount');

        return [
            Stat::make('Income', number_format($income, 2))
                ->color('success'),
            Stat::make('Expenses', number_format(abs($expense), 2))
                ->color('danger'),
            Stat::make('Balance', number_format($income + $expense, 2))
                ->color($income + $expense >= 0 ? 'success' : 'danger'),
        ];
    }
}
