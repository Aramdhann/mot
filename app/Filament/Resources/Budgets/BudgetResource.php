<?php

namespace App\Filament\Resources\Budgets;

use App\Filament\Resources\Budgets\Pages\ManageBudgets;
use App\Models\Budget;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('category')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->helperText('Transactions with this category count against the budget.'),
                TextInput::make('amount')
                    ->label('Monthly limit')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('IDR'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Monthly limit')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('spent')
                    ->label('Spent this month')
                    ->state(fn (Budget $record): float => $record->spent_this_month)
                    ->money('IDR')
                    ->color(fn (Budget $record): string => $record->spent_this_month > (float) $record->amount ? 'danger' : ($record->spent_this_month > 0.7 * (float) $record->amount ? 'warning' : 'success')),
                TextColumn::make('remaining')
                    ->label('Remaining')
                    ->state(fn (Budget $record): float => (float) $record->amount - $record->spent_this_month)
                    ->money('IDR')
                    ->color(fn (Budget $record): string => ($record->spent_this_month > (float) $record->amount) ? 'danger' : 'success'),
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
            'index' => ManageBudgets::route('/'),
        ];
    }
}
