<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class BudgetProgress extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Budgets this month')
            ->query(fn () => Budget::query()->orderBy('category'))
            ->paginated(false)
            ->headerActions([
                Action::make('viewAll')
                    ->label('Manage')
                    ->icon('heroicon-m-arrow-right')
                    ->color('gray')
                    ->url(route('filament.admin.resources.budgets.index')),
            ])
            ->description(fn (): string => now()->translatedFormat('F Y'))
            ->columns([
                TextColumn::make('category')
                    ->weight('font-medium'),
                TextColumn::make('amount')
                    ->label('Limit')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('spent')
                    ->label('Spent')
                    ->money('IDR')
                    ->alignEnd()
                    ->color(fn (Budget $record): string => $record->spent_this_month > (float) $record->amount ? 'danger' : ($record->spent_this_month > 0.7 * (float) $record->amount ? 'warning' : 'success'))
                    ->state(fn (Budget $record): float => $record->spent_this_month),
                TextColumn::make('remaining')
                    ->money('IDR')
                    ->alignEnd()
                    ->color(fn (Budget $record): string => ($record->spent_this_month > (float) $record->amount) ? 'danger' : 'success')
                    ->state(fn (Budget $record): float => (float) $record->amount - $record->spent_this_month),
            ]);
    }
}
