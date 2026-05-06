<?php

namespace App\Filament\Resources\EmbalsesRans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmbalsesRanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('er_nombre')
                    ->required(),
                TextInput::make('er_provincia')
                    ->required(),
                TextInput::make('er_umbral1')
                    ->required()
                    ->numeric(),
                TextInput::make('er_umbral2')
                    ->required()
                    ->numeric(),
                TextInput::make('er_umbral3')
                    ->required()
                    ->numeric(),
                TextInput::make('er_tag_ip21')
                    ->required(),
                TextInput::make('er_tag_volumen')
                    ->required(),
                TextInput::make('er_tag_digital_ip21')
                    ->required(),
                TextInput::make('er_activo')
                    ->required()
                    ->numeric(),
                TextInput::make('er_municipio'),
                TextInput::make('er_rio'),
                TextInput::make('er_capacidad')
                    ->numeric(),
                TextInput::make('er_ultimo_nivel_alerta')
                    ->numeric(),
                TextInput::make('er_comunidad_autonoma_id')
                    ->required()
                    ->numeric(),
                TextInput::make('er_ccaa_influencia'),
                TextInput::make('er_titularidad'),
            ]);
    }
}
