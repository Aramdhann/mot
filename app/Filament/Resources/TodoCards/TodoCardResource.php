<?php

namespace App\Filament\Resources\TodoCards;

use App\Filament\Resources\TodoCards\Pages\ManageTodoCards;
use App\Models\TodoCard;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TodoCardResource extends Resource
{
    protected static ?string $model = TodoCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Repeater::make('items')
                    ->relationship()
                    ->orderColumn('sort')
                    ->label('Checklist')
                    ->helperText('Tick the \"Done\" switch to check an item off. Drag the grip handle on the left of an item to change its position.')
                    ->addActionLabel('Add item')
                    ->itemLabel(fn (array $state): string => $state['content'] ?? 'New item')
                    ->schema([
                        TextInput::make('content')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['md' => 4]),
                        Toggle::make('is_done')
                            ->label('Done')
                            ->inline()
                            ->columnSpan(['md' => 2]),
                    ])
                    ->columns([
                        'md' => 6,
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable()
            ->description('Tip: search matches titles and checklist items. Drag a row to reorder your cards.')
            // card title OR any checklist item content — LIKE is enough at this scale (no FTS)
            ->searchUsing(fn (Builder $query, string $search) => $query->where(
                fn (Builder $q) => $q->where('todo_cards.title', 'like', "%{$search}%")
                    ->orWhereHas('items', fn (Builder $i) => $i->where('content', 'like', "%{$search}%"))
            ))
            ->columns([
                TextColumn::make('title')
                    ->weight('font-bold'),
                TextColumn::make('progress')
                    ->badge()
                    // ponytail: N+1 items per page of cards — fine at personal scale
                    ->state(fn (TodoCard $record): string => $record->items->where('is_done')->count().' / '.$record->items->count().' done')
                    ->color(fn (TodoCard $record): string => $record->items->isNotEmpty() && $record->items->where('is_done')->count() === $record->items->count() ? 'success' : 'gray'),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
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
            'index' => ManageTodoCards::route('/'),
        ];
    }
}
