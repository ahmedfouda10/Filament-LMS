<?php

namespace App\Filament\Resources\PaymentLogs\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class PaymentLogTable
{
    public static function configure(Table $table): Table
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
}
