<?php

namespace App\Filament\Resources\EmbalsesRans;

use App\Filament\Resources\EmbalsesRans\Pages\CreateEmbalsesRan;
use App\Filament\Resources\EmbalsesRans\Pages\EditEmbalsesRan;
use App\Filament\Resources\EmbalsesRans\Pages\ListEmbalsesRans;
use App\Filament\Resources\EmbalsesRans\Schemas\EmbalsesRanForm;
use App\Filament\Resources\EmbalsesRans\Tables\EmbalsesRansTable;
use App\Models\EmbalsesRan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmbalsesRanResource extends Resource
{
    protected static ?string $model = EmbalsesRan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return EmbalsesRanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmbalsesRansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmbalsesRans::route('/'),
            'create' => CreateEmbalsesRan::route('/create'),
            'edit' => EditEmbalsesRan::route('/{record}/edit'),
        ];
    }
}
