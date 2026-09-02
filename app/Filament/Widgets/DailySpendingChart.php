<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class DailySpendingChart extends ChartWidget
{
    protected ?string $description = 'Expenses + loan payments';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string|null
    {
        return 'Spending by day — '.$this->currentMonth()->translatedFormat('F Y');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /** Last 6 months, newest first. */
    protected function getFilters(): ?array
    {
        $filters = [];
        for ($i = 0; $i < 6; $i++) {
            $month = now()->startOfMonth()->subMonths($i);
            $filters[$month->format('Y-m')] = $month->translatedFormat('F Y');
        }

        return $filters;
    }

    protected function getData(): array
    {
        $month = $this->currentMonth();

        // ponytail: extract() is Postgres-specific — switch to DAY() on MySQL if you ever migrate
        $daily = Transaction::query()
            ->whereIn('type', [TransactionType::Expense, TransactionType::LoanPayment])
            ->whereBetween('occurred_on', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->selectRaw('extract(day from occurred_on) as day, sum(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = $month->daysInMonth;

        return [
            'datasets' => [
                [
                    'label' => 'Spent',
                    'data' => array_map(fn (int $d): float => round((float) ($daily[$d] ?? 0)), range(1, $days)),
                    'backgroundColor' => '#e11d48',
                ],
            ],
            'labels' => range(1, $days),
        ];
    }

    protected function currentMonth(): \Illuminate\Support\Carbon
    {
        return now()->createFromFormat('Y-m', $this->filter ?? now()->format('Y-m'))->startOfMonth();
    }
}
