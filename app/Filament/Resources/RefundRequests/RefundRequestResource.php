<?php

namespace App\Filament\Resources\RefundRequests;

use App\Filament\Resources\RefundRequests\Pages;
use App\Filament\Resources\RefundRequests\Tables\RefundRequestTable;
use App\Models\RefundRequest;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class RefundRequestResource extends Resource
{
    protected static ?string $model = RefundRequest::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static \UnitEnum|string|null $navigationGroup = 'Sales';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool { return false; }

    public static function table(Table $table): Table
    {
        return RefundRequestTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRefundRequests::route('/')];
    }
}
