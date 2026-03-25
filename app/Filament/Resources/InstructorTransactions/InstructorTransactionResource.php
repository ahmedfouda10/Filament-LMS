<?php

namespace App\Filament\Resources\InstructorTransactions;

use App\Filament\Resources\InstructorTransactions\Pages\ListInstructorTransactions;
use App\Filament\Resources\InstructorTransactions\Pages\ViewInstructorTransaction;
use App\Filament\Resources\InstructorTransactions\Schemas\InfolistSchema;
use App\Filament\Resources\InstructorTransactions\Tables\InstructorTransactionTable;
use App\Models\InstructorTransaction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class InstructorTransactionResource extends Resource
{
    protected static ?string $model = InstructorTransaction::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected static \UnitEnum|string|null $navigationGroup = 'Revenue';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Transactions';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InfolistSchema::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstructorTransactionTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstructorTransactions::route('/'),
            'view' => ViewInstructorTransaction::route('/{record}'),
        ];
    }
}
