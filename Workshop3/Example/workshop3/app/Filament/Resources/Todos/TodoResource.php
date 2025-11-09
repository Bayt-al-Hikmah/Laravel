<?php

namespace App\Filament\Resources\Todos;

use App\Filament\Resources\Todos\Pages\CreateTodo;
use App\Filament\Resources\Todos\Pages\EditTodo;
use App\Filament\Resources\Todos\Pages\ListTodos;
use App\Filament\Resources\Todos\Schemas\TodoForm;
use App\Filament\Resources\Todos\Tables\TodosTable;
use App\Models\Todo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TodoResource extends Resource
{
    protected static ?string $model = Todo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Tasks';

    public static function form(Schema $schema): Schema
    {
        return TodoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TodosTable::configure($table);
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
            'index' => ListTodos::route('/'),
            'create' => CreateTodo::route('/create'),
            'edit' => EditTodo::route('/{record}/edit'),
        ];
    }
}
