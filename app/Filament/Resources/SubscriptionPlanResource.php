<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Filament\Resources\SubscriptionPlanResource\Pages\CreateSubscriptionPlan;
use App\Filament\Resources\SubscriptionPlanResource\Pages\EditSubscriptionPlan;
use App\Filament\Resources\SubscriptionPlanResource\Pages\ListSubscriptionPlans;
use App\Models\SubscriptionPlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static \UnitEnum|string|null $navigationGroup = 'Subscriptions';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_months')
                    ->label('Duration')
                    ->suffix(' months')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_month')
                    ->label('Price/Month')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('savings_percentage')
                    ->label('Savings')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label('Subscriptions')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_popular')
                    ->label('Popular'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('is_popular')
                    ->label('Popular'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionPlans::route('/'),
            'create' => CreateSubscriptionPlan::route('/create'),
            'edit' => EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
