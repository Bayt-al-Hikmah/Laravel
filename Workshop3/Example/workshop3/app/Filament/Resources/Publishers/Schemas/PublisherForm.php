<?php

namespace App\Filament\Resources\Publishers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PublisherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                TextInput::make('name')
                    ->required()
                    ->maxLength(200),
                    
                TextInput::make('address')
                    ->required()
                    ->maxLength(200),
            ]);
    }
}
