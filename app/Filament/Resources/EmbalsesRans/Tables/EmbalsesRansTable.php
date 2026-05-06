<?php

namespace App\Filament\Resources\EmbalsesRans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmbalsesRansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('er_codigo')
                    ->searchable(),
                TextColumn::make('er_nombre')
                    ->searchable(),
                TextColumn::make('er_provincia')
                    ->searchable(),
                TextColumn::make('er_umbral1')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('er_umbral2')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('er_umbral3')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('er_tag_ip21')
                    ->searchable(),
                TextColumn::make('er_tag_volumen')
                    ->searchable(),
                TextColumn::make('er_tag_digital_ip21')
                    ->searchable(),
                TextColumn::make('er_activo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('er_municipio')
                    ->searchable(),
                TextColumn::make('er_rio')
                    ->searchable(),
                TextColumn::make('er_capacidad')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('er_ultimo_nivel_alerta')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('er_comunidad_autonoma_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('er_ccaa_influencia')
                    ->searchable(),
                TextColumn::make('er_titularidad')
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
