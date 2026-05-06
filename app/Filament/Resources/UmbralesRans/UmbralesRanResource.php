<?php

namespace App\Filament\Resources\UmbralesRans;

use App\Filament\Resources\UmbralesRans\Pages\CreateUmbralesRan;
use App\Filament\Resources\UmbralesRans\Pages\EditUmbralesRan;
use App\Filament\Resources\UmbralesRans\Pages\ListUmbralesRans;
use App\Filament\Resources\UmbralesRans\Schemas\UmbralesRanForm;
use App\Filament\Resources\UmbralesRans\Tables\UmbralesRansTable;
use App\Models\UmbralesRan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UmbralesRanResource extends Resource
{
    protected static ?string $model = UmbralesRan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UmbralesRanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmbralesRansTable::configure($table);
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
            'index' => ListUmbralesRans::route('/'),
            'create' => CreateUmbralesRan::route('/create'),
            'edit' => EditUmbralesRan::route('/{record}/edit'),
        ];
    }
}
