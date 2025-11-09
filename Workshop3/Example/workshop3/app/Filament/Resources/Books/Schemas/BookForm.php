<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;


class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(200),
                
                Select::make('publisher_id')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->required(),
                
                Select::make('authors')
                    ->relationship('authors', 'first_name') 
                    ->multiple()
                    ->preload(),
                
                DatePicker::make('publish_date'),
                
                Toggle::make('available') 
                    ->required(),

            ]);
    }
}
