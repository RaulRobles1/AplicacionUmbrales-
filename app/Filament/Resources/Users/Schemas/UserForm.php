<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('password')
                    ->password()
                    ->required(),

                DateTimePicker::make('last_login'),

                Toggle::make('is_superuser')
                    ->required(),

                TextInput::make('username')
                    ->required(),

                TextInput::make('first_name')
                    ->required(),

                TextInput::make('last_name')
                    ->required(),

                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),

                Toggle::make('is_staff')
                    ->required(),

                Toggle::make('is_active')
                    ->required(),

                DateTimePicker::make('date_joined')
                    ->required(),

                Select::make('groups')
                    ->relationship('groups', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->label('Groups'),
            ]);
    }
}
