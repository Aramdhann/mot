<?php

namespace App\Filament\Widgets;

use App\Models\Wallet;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class WalletBalances extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Wallets')
            ->query(fn () => Wallet::query()->orderBy('name'))
            ->paginated(false)
            ->headerActions([
                Action::make('viewAll')
                    ->label('Manage')
                    ->icon('heroicon-m-arrow-right')
                    ->color('gray')
                    ->url(route('filament.admin.resources.wallets.index')),
            ])
            ->columns([
                TextColumn::make('name')
                    ->weight('font-medium'),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'warning',
                        'bank' => 'success',
                        'ewallet' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('balance')
                    ->money('IDR')
                    ->alignEnd()
                    ->color(fn (Wallet $record): string => $record->balance >= 0 ? 'success' : 'danger')
                    ->state(fn (Wallet $record): float => $record->balance),
            ]);
    }
}
