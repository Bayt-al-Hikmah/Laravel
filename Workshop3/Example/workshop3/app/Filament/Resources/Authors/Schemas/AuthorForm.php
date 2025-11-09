<?php

namespace App\Filament\Resources\Authors\Schemas;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(200),
                    
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(200),
            ]);
    }
}
