<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Carbon;

class DailySpendingList extends TableWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public function getTableRecordKey($record): string
    {
        return $record->occurred_on->format('Y-m-d');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Spending by day')
            ->description('Expenses + loan payments')
            ->defaultSort('occurred_on', 'desc')
            ->defaultKeySort(false)
            ->query(fn () => $this->spendingByDay())
            ->paginated(false)
            ->emptyStateHeading('No spending this month')
            ->filters([
                SelectFilter::make('month')
                    ->options(fn (): array => $this->monthOptions())
                    ->default(now()->format('Y-m'))
                    ->query(function ($query, array $data) {
                        $month = $this->currentMonth($data['month'] ?? null);

                        return $query->whereBetween('occurred_on', [$month, $month->copy()->endOfMonth()]);
                    }),
            ])
            ->columns([
                TextColumn::make('occurred_on')
                    ->label('Day')
                    ->date('D, d M Y')
                    ->weight('font-medium'),
                TextColumn::make('total')
                    ->label('Spent')
                    ->money('IDR')
                    ->alignEnd()
                    ->color('danger'),
            ]);
    }

    protected function spendingByDay()
    {
        // month where-clause comes solely from the SelectFilter (its default applies on first load)

        // ponytail: extract() is Postgres-specific — switch to DAY() on MySQL if you ever migrate
        return Transaction::query()
            ->whereIn('type', [TransactionType::Expense, TransactionType::LoanPayment])
            ->selectRaw('occurred_on, sum(amount) as total')
            ->groupBy('occurred_on');
    }

    protected function currentMonth(?string $key): Carbon
    {
        return now()->createFromFormat('Y-m', $key ?: now()->format('Y-m'))->startOfMonth();
    }

    protected function monthOptions(): array
    {
        $options = [];
        for ($i = 0; $i < 6; $i++) {
            $m = now()->startOfMonth()->subMonths($i);
            $options[$m->format('Y-m')] = $m->translatedFormat('F Y');
        }

        return $options;
    }
}
