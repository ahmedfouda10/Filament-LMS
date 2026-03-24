<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefundRequestResource\Pages;
use App\Filament\Resources\RefundRequestResource\Pages\ListRefundRequests;
use App\Models\RefundRequest;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
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

    public static function getPages(): array
    {
        return ['index' => ListRefundRequests::route('/')];
    }
}
