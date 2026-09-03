<?php

namespace App\Filament\Resources\LinkCards\Pages;

use App\Filament\Resources\LinkCards\LinkCardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLinkCards extends ManageRecords
{
    protected static string $resource = LinkCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
