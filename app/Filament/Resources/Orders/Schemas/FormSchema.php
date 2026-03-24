<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FormSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
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
}
