<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Plan Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->maxLength(1000),
                Forms\Components\TextInput::make('duration_months')
                    ->label('Duration (Months)')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('price_per_month')
                    ->label('Price per Month (EGP)')
                    ->required()
                    ->numeric()
                    ->prefix('EGP')
                    ->minValue(0),
                Forms\Components\TextInput::make('total_price')
                    ->label('Total Price (EGP)')
                    ->required()
                    ->numeric()
                    ->prefix('EGP')
                    ->minValue(0),
                Forms\Components\TextInput::make('savings_percentage')
                    ->label('Savings (%)')
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100),
            ])->columns(2),
            Section::make('Status')->schema([
                Forms\Components\Toggle::make('is_popular')
                    ->label('Popular Plan')
                    ->default(false),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])->columns(2),
            Section::make('Features')->schema([
                Forms\Components\Repeater::make('features')
                    ->simple(
                        Forms\Components\TextInput::make('feature')
                            ->required(),
                    )
                    ->defaultItems(1)
                    ->reorderable()
                    ->addActionLabel('Add Feature'),
            ])->collapsible(),
        ]);
    }
}
