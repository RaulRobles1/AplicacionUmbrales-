<?php

namespace App\Filament\Resources\EmbalsesRans\Pages;

use App\Filament\Resources\EmbalsesRans\EmbalsesRanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmbalsesRan extends EditRecord
{
    protected static string $resource = EmbalsesRanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
