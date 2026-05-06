<?php

namespace App\Filament\Resources\UmbralesRans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UmbralesRanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ur_codigo')
                    ->required(),
                TextInput::make('ur_nombre')
                    ->required(),
                TextInput::make('ur_provincia')
                    ->required(),
                TextInput::make('ur_umbral1')
                    ->required()
                    ->numeric(),
                TextInput::make('ur_umbral2')
                    ->required()
                    ->numeric(),
                TextInput::make('ur_umbral3')
                    ->required()
                    ->numeric(),
                TextInput::make('ur_parametro')
                    ->required(),
                TextInput::make('ur_tag_ip21'),
                TextInput::make('ur_tag_ip21_caudal'),
                TextInput::make('ur_tag_digital_ip21'),
                TextInput::make('ur_activo')
                    ->required()
                    ->numeric(),
                TextInput::make('ur_rio'),
                TextInput::make('ur_municipio'),
                TextInput::make('ur_caudal1')
                    ->numeric(),
                TextInput::make('ur_caudal2')
                    ->numeric(),
                TextInput::make('ur_caudal3')
                    ->numeric(),
                TextInput::make('ur_ultimo_nivel_alerta')
                    ->numeric(),
                TextInput::make('ur_comunidad_autonoma_id')
                    ->required()
                    ->numeric(),
                TextInput::make('ur_zona_explotacion'),
                TextInput::make('ur_ccaa_influencia'),
            ]);
    }
}
