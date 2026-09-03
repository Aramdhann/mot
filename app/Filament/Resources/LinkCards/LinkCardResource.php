<?php

namespace App\Filament\Resources\LinkCards;

use App\Filament\Resources\LinkCards\Pages\ManageLinkCards;
use App\Models\LinkCard;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LinkCardResource extends Resource
{
    protected static ?string $model = LinkCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(2)
                    ->columnSpanFull(),
                Repeater::make('items')
                    ->relationship()
                    ->orderColumn('sort')
                    ->label('Links to compare')
                    ->helperText('Each item is one brand/shop. Use the open-link icon (top right of an item) to open it in a new tab, and drag the grip handle to change its position.')
                    ->addActionLabel('Add link')
                    ->collapsible()
                    ->collapseAllAction(fn (Action $action) => $action->color('primary'))
                    ->expandAllAction(fn (Action $action) => $action->color('primary'))
                    ->itemLabel(fn (array $state): string => trim(($state['title'] ?? 'New link').(isset($state['price']) && $state['price'] !== '' && $state['price'] !== null ? ' — Rp '.number_format((float) $state['price'], 0, ',', '.') : '')))
                    ->extraItemActions([
                        Action::make('openLink')
                            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                            ->color('gray')
                            ->tooltip('Open in new tab')
                            ->url(function (array $arguments, Repeater $component): ?string {
                                $url = $component->getRawState()[$arguments['item']]['url'] ?? null;

                                return filled($url) ? $url : null;
                            })
                            ->openUrlInNewTab(),
                    ])
                    ->schema([
                        TextInput::make('title')
                            ->label('Brand / shop')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['md' => 4]),
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('Rp')
                            ->columnSpan(['md' => 2]),
                        TextInput::make('url')
                            ->url()
                            ->required()
                            ->maxLength(2048)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
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
            ->description('Tip: search matches titles, brands, links and descriptions. Drag a row to reorder.')
            ->searchUsing(fn (Builder $query, string $search) => $query->where(
                fn (Builder $q) => $q->where('link_cards.title', 'like', "%{$search}%")
                    ->orWhere('link_cards.description', 'like', "%{$search}%")
                    ->orWhereHas('items', fn (Builder $i) => $i
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%"))
            ))
            ->columns([
                TextColumn::make('title')
                    ->weight('font-bold'),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Links')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray'),
                TextColumn::make('brands')
                    // ponytail: N+1 items per page of cards — fine at family scale
                    ->state(fn (LinkCard $record): string => $record->items->pluck('title')->implode(' · '))
                    ->limit(60)
                    ->wrap(),
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
            'index' => ManageLinkCards::route('/'),
        ];
    }
}
