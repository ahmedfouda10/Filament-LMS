<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Resources\SubscriptionResource\Pages\ListSubscriptions;
use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-credit-card';
    protected static \UnitEnum|string|null $navigationGroup = 'Subscriptions';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->schema([
            Forms\Components\TextInput::make('student_name')
                ->label('Student')
                ->formatStateUsing(fn(Subscription $record): string => $record->user?->name ?? '-')
                ->disabled(),
            Forms\Components\TextInput::make('plan_name')
                ->label('Plan')
                ->formatStateUsing(fn(Subscription $record): string => $record->plan?->name ?? '-')
                ->disabled(),
            Forms\Components\TextInput::make('status')
                ->label('Status')
                ->disabled(),
            Forms\Components\DatePicker::make('start_date')
                ->label('Start Date')
                ->disabled(),
            Forms\Components\DatePicker::make('end_date')
                ->label('End Date')
                ->disabled(),
            Forms\Components\Toggle::make('auto_renew')
                ->label('Auto Renew')
                ->disabled(),
            Forms\Components\DateTimePicker::make('created_at')
                ->label('Created At')
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'expiring' => 'warning',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('auto_renew')
                    ->label('Auto Renew')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'expiring' => 'Expiring',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name')
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('3xl'),
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Subscription')
                    ->modalDescription('Are you sure you want to cancel this subscription? This action cannot be undone.')
                    ->visible(fn(Subscription $record): bool => in_array($record->status, ['active', 'expiring']))
                    ->action(function (Subscription $record): void {
                        $record->update(['status' => 'cancelled', 'auto_renew' => false]);
                        Notification::make()
                            ->title('Subscription cancelled successfully')
                            ->success()
                            ->send();
                    }),
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
            'index' => ListSubscriptions::route('/'),
        ];
    }
}
