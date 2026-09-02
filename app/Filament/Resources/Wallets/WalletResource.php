<?php

namespace App\Filament\Resources\Wallets;

use App\Filament\Resources\Wallets\Pages\ManageWallets;
use App\Models\Wallet;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                Select::make('type')
                    ->options([
                        'cash' => 'Cash',
                        'bank' => 'Bank',
                        'ewallet' => 'E-Wallet',
                        'other' => 'Other',
                    ])
                    ->default('cash')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
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
                    ->color(fn (Wallet $record): string => $record->balance >= 0 ? 'success' : 'danger')
                    ->state(fn (Wallet $record): float => $record->balance),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWallets::route('/'),
        ];
    }
}
