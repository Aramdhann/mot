<?php

namespace App\Filament\Resources\Loans;

use App\Filament\Resources\Loans\Pages\ManageLoans;
use App\Models\Loan;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->helperText('e.g. Motor, KPR, Paylater'),
                TextInput::make('principal')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('IDR'),
                DatePicker::make('started_on')
                    ->default(now())
                    ->required(),
                Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('principal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('paid')
                    ->label('Paid')
                    ->state(fn (Loan $record): float => $record->paid)
                    ->money('IDR'),
                TextColumn::make('remaining')
                    ->state(fn (Loan $record): float => $record->remaining)
                    ->money('IDR')
                    ->color(fn (Loan $record): string => $record->remaining <= 0 ? 'success' : 'danger'),
                TextColumn::make('started_on')
                    ->date()
                    ->sortable(),
                TextColumn::make('note')
                    ->limit(40)
                    ->placeholder('—')
                    ->tooltip(fn (Loan $record): ?string => $record->note),
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
            'index' => ManageLoans::route('/'),
        ];
    }
}
