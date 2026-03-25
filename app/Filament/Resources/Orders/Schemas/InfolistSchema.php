<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InfolistSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
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
}
