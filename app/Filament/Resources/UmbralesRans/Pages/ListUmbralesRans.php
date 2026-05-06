<?php

namespace App\Filament\Resources\UmbralesRans\Pages;

use App\Filament\Resources\UmbralesRans\UmbralesRanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUmbralesRans extends ListRecords
{
    protected static string $resource = UmbralesRanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
