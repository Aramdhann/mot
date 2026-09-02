<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestTransactions extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest transactions')
            ->query(fn () => Transaction::query()->with(['wallet', 'transferToWallet', 'loan'])->orderByDesc('occurred_on')->orderByDesc('id')->limit(8))
            ->paginated(false)
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->icon('heroicon-m-arrow-right')
                    ->color('gray')
                    ->url(route('filament.admin.resources.transactions.index')),
            ])
            ->columns([
                TextColumn::make('occurred_on')
                    ->date(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state->getLabel())
                    ->color(fn ($state): string => $state->getColor()),
                TextColumn::make('wallet')
                    ->label('Wallet')
                    ->formatStateUsing(fn (Transaction $record): string => $record->wallet?->name
                        .($record->transferToWallet ? ' → '.$record->transferToWallet->name : '')),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->color(fn (Transaction $record): string => $record->type->getColor()),
                TextColumn::make('category')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('description')
                    ->limit(25)
                    ->placeholder('—'),
            ]);
    }
}
