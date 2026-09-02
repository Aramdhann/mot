<?php

namespace App\Filament\Resources\Transactions;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\Pages\ManageTransactions;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Wallet;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(TransactionType::class)
                    ->default(TransactionType::Expense->value)
                    ->live()
                    ->required(),
                Select::make('wallet_id')
                    ->label('Wallet')
                    ->options(fn (): array => Wallet::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('IDR'),
                TextInput::make('category')
                    ->required(fn (Get $get): bool => $get('type') === TransactionType::Expense->value)
                    ->visible(fn (Get $get): bool => in_array($get('type'), [TransactionType::Income->value, TransactionType::Expense->value]))
                    ->maxLength(50),
                Select::make('transfer_to_wallet_id')
                    ->label('To wallet')
                    ->options(fn (): array => Wallet::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->visible(fn (Get $get): bool => $get('type') === TransactionType::Transfer->value)
                    ->required(fn (Get $get): bool => $get('type') === TransactionType::Transfer->value)
                    ->different('wallet_id')
                    ->helperText('Source wallet is the "Wallet" field above.'),
                Select::make('loan_id')
                    ->label('Loan')
                    ->options(fn (): array => Loan::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->visible(fn (Get $get): bool => $get('type') === TransactionType::LoanPayment->value)
                    ->required(fn (Get $get): bool => $get('type') === TransactionType::LoanPayment->value),
                TextInput::make('description')
                    ->maxLength(255),
                DatePicker::make('occurred_on')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_on')
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (TransactionType $state): string => $state->getLabel())
                    ->color(fn (TransactionType $state): string => $state->getColor()),
                TextColumn::make('wallet.name')
                    ->label('Wallet')
                    ->formatStateUsing(fn (Transaction $record): string => $record->wallet?->name
                        .($record->transferToWallet ? ' → '.$record->transferToWallet->name : '')),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->formatStateUsing(fn (Transaction $record): string => number_format((float) $record->amount, 0))
                    ->color(fn (Transaction $record): string => $record->type->getColor()),
                TextColumn::make('category')
                    ->badge()
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('description')
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('loan.name')
                    ->placeholder('—'),
            ])
            ->defaultSort('occurred_on', 'desc')
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
            'index' => ManageTransactions::route('/'),
        ];
    }
}
