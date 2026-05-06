<?php

namespace App\Filament\Resources\UmbralesRans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UmbralesRansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ur_codigo')
                    ->searchable(),
                TextColumn::make('ur_nombre')
                    ->searchable(),
                TextColumn::make('ur_provincia')
                    ->searchable(),
                TextColumn::make('ur_umbral1')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ur_umbral2')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ur_umbral3')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ur_parametro')
                    ->searchable(),
                TextColumn::make('ur_tag_ip21')
                    ->searchable(),
                TextColumn::make('ur_tag_ip21_caudal')
                    ->searchable(),
                TextColumn::make('ur_tag_digital_ip21')
                    ->searchable(),
                TextColumn::make('ur_activo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ur_rio')
                    ->searchable(),
                TextColumn::make('ur_municipio')
                    ->searchable(),
                TextColumn::make('ur_caudal1')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ur_caudal2')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ur_caudal3')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ur_ultimo_nivel_alerta')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ur_comunidad_autonoma_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ur_zona_explotacion')
                    ->searchable(),
                TextColumn::make('ur_ccaa_influencia')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
