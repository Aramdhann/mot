<?php

namespace App\Filament\Resources\Notes;

use App\Filament\Resources\Notes\Pages\ManageNotes;
use App\Models\Note;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('content')
                    ->rows(12)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable()
            ->searchUsing(fn (Builder $query, string $search) => $query->search($search))
            ->emptyStateDescription("Tip: search finds whole words in the title and content — partial words still match, but misspellings don't. Try a shorter word.")
            ->columns([
                TextColumn::make('title')
                    ->weight('font-bold'),
                TextColumn::make('content')
                    ->limit(100)
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
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
            'index' => ManageNotes::route('/'),
        ];
    }
}
