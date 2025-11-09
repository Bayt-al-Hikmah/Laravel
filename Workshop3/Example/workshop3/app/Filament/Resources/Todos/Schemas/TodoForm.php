<?php

namespace App\Filament\Resources\Todos\Schemas;

use Filament\Schemas\Schema;

class TodoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
