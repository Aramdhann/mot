<?php

namespace App\Filament\Resources\TodoCards\Pages;

use App\Filament\Resources\TodoCards\TodoCardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTodoCards extends ManageRecords
{
    protected static string $resource = TodoCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
