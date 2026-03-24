<?php

namespace App\Filament\Resources\PaymentLogs;

use App\Filament\Resources\PaymentLogs\Pages;
use App\Filament\Resources\PaymentLogs\Tables\PaymentLogTable;
use App\Models\PaymentLog;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PaymentLogResource extends Resource
{
    protected static ?string $model = PaymentLog::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';
    protected static \UnitEnum|string|null $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Payment Logs';
    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool { return false; }

    public static function table(Table $table): Table
    {
        return PaymentLogTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPaymentLogs::route('/')];
    }
}
