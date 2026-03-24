<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static \UnitEnum|string|null $navigationGroup = 'Sales';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Order Details')->schema([
                Forms\Components\TextInput::make('order_number')
                    ->disabled(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('subtotal')
                    ->prefix('EGP')
                    ->disabled(),
                Forms\Components\TextInput::make('discount')
                    ->prefix('EGP')
                    ->disabled(),
                Forms\Components\TextInput::make('total')
                    ->prefix('EGP')
                    ->disabled(),
                Forms\Components\TextInput::make('payment_method')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
                Forms\Components\DateTimePicker::make('paid_at')
                    ->disabled(),
            ])->columns(2),

            Section::make('Billing Address')->schema([
                Forms\Components\TextInput::make('billing_street')->disabled(),
                Forms\Components\TextInput::make('billing_city')->disabled(),
                Forms\Components\TextInput::make('billing_state')->disabled(),
                Forms\Components\TextInput::make('billing_country')->disabled(),
                Forms\Components\TextInput::make('billing_postal_code')->disabled(),
            ])->columns(2)->collapsible(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Order Information')->schema([
                Infolists\Components\TextEntry::make('order_number')
                    ->label('Order Number')
                    ->weight('bold'),
                Infolists\Components\TextEntry::make('user.name')
                    ->label('Customer'),
                Infolists\Components\TextEntry::make('user.email')
                    ->label('Email'),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        'refunded' => 'gray',
                        default => 'secondary',
                    }),
                Infolists\Components\TextEntry::make('payment_method')
                    ->badge(),
                Infolists\Components\TextEntry::make('paid_at')
                    ->dateTime(),
            ])->columns(3),

            Section::make('Pricing')->schema([
                Infolists\Components\TextEntry::make('subtotal')
                    ->money('EGP'),
                Infolists\Components\TextEntry::make('discount')
                    ->money('EGP'),
                Infolists\Components\TextEntry::make('total')
                    ->money('EGP')
                    ->weight('bold'),
                Infolists\Components\TextEntry::make('promoCode.code')
                    ->label('Promo Code')
                    ->placeholder('None'),
            ])->columns(4),

            Section::make('Billing Address')->schema([
                Infolists\Components\TextEntry::make('billing_street'),
                Infolists\Components\TextEntry::make('billing_city'),
                Infolists\Components\TextEntry::make('billing_state'),
                Infolists\Components\TextEntry::make('billing_country'),
                Infolists\Components\TextEntry::make('billing_postal_code'),
            ])->columns(3)->collapsible(),

            Section::make('Timestamps')->schema([
                Infolists\Components\TextEntry::make('created_at')
                    ->dateTime(),
                Infolists\Components\TextEntry::make('updated_at')
                    ->dateTime(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
                Tables\Columns\TextColumn::make('subtotal')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('EGP')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->colors([
                        'primary' => 'card',
                        'success' => 'wallet',
                        'warning' => 'cash',
                    ]),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger' => 'cancelled',
                        'gray' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not Paid'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'card' => 'Card',
                        'wallet' => 'Wallet',
                        'cash' => 'Cash',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    Action::make('mark_completed')
                        ->label('Mark Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(Order $record) => $record->status === 'pending')
                        ->action(fn(Order $record) => $record->update([
                            'status' => 'completed',
                            'paid_at' => $record->paid_at ?? now(),
                        ])),
                    Action::make('cancel')
                        ->label('Cancel Order')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn(Order $record) => in_array($record->status, ['pending']))
                        ->action(fn(Order $record) => $record->update(['status' => 'cancelled'])),
                    Action::make('refund')
                        ->label('Refund Order')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn(Order $record) => $record->status === 'completed')
                        ->action(fn(Order $record) => $record->update(['status' => 'refunded'])),
                ]),
            ])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
