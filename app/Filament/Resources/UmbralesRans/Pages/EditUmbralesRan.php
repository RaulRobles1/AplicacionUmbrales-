<?php

namespace App\Filament\Resources\UmbralesRans\Pages;

use App\Filament\Resources\UmbralesRans\UmbralesRanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUmbralesRan extends EditRecord
{
    protected static string $resource = UmbralesRanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
