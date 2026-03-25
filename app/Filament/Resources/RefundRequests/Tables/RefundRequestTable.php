<?php

namespace App\Filament\Resources\RefundRequests\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class RefundRequestTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->label('Order')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Student')->searchable(),
                Tables\Columns\TextColumn::make('reason')->limit(50),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger' }),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('resolved_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
            ])
            ->recordAction(null)
            ->headerActions([
            ])
            ->emptyStateActions([
            ]);
    }
}
