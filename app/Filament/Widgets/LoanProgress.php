<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LoanProgress extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Loans')
            ->query(fn () => Loan::query()->orderBy('name'))
            ->paginated(false)
            ->headerActions([
                Action::make('viewAll')
                    ->label('Manage')
                    ->icon('heroicon-m-arrow-right')
                    ->color('gray')
                    ->url(route('filament.admin.resources.loans.index')),
            ])
            ->columns([
                TextColumn::make('name')
                    ->weight('font-medium'),
                TextColumn::make('principal')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('paid')
                    ->money('IDR')
                    ->alignEnd()
                    ->state(fn (Loan $record): float => $record->paid),
                TextColumn::make('remaining')
                    ->money('IDR')
                    ->alignEnd()
                    ->color(fn (Loan $record): string => $record->remaining <= 0 ? 'success' : 'danger')
                    ->state(fn (Loan $record): float => $record->remaining),
                TextColumn::make('note')
                    ->label('Note')
                    ->limit(25)
                    ->placeholder('—')
                    ->tooltip(fn (Loan $record): ?string => $record->note),
            ]);
    }
}
