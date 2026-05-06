<?php

namespace App\Filament\Resources\EmbalsesRans\Pages;

use App\Filament\Resources\EmbalsesRans\EmbalsesRanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmbalsesRans extends ListRecords
{
    protected static string $resource = EmbalsesRanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
