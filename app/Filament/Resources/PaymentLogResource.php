<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentLogResource\Pages;
use App\Models\PaymentLog;
use Filament\Resources\Resource;
use Filament\Tables;
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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->label('Order')->searchable(),
                Tables\Columns\TextColumn::make('transaction_id')->searchable(),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('amount')->money('EGP'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) { 'success' => 'success', 'failed' => 'danger', 'pending' => 'warning', 'refunded' => 'gray' }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['success' => 'Success', 'failed' => 'Failed', 'pending' => 'Pending', 'refunded' => 'Refunded']),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPaymentLogs::route('/')];
    }
}
