<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstructorTransactionResource\Pages;
use App\Filament\Resources\InstructorTransactionResource\Pages\ListInstructorTransactions;
use App\Filament\Resources\InstructorTransactionResource\Pages\ViewInstructorTransaction;
use App\Models\InstructorTransaction;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InstructorTransactionResource extends Resource
{
    protected static ?string $model = InstructorTransaction::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected static \UnitEnum|string|null $navigationGroup = 'Revenue';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Transactions';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Transaction Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_number')
                            ->label('Transaction #'),
                        Infolists\Components\TextEntry::make('instructor.name')
                            ->label('Instructor'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'sale' => 'success',
                                'payout' => 'info',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'cleared' => 'info',
                                'completed' => 'success',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('course.title')
                            ->label('Course'),
                        Infolists\Components\TextEntry::make('payout_method')
                            ->label('Payout Method'),
                    ])->columns(2),
                Section::make('Financial Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('amount')
                            ->label('Amount')
                            ->money('EGP'),
                        Infolists\Components\TextEntry::make('platform_fee')
                            ->label('Platform Fee')
                            ->money('EGP'),
                        Infolists\Components\TextEntry::make('net_amount')
                            ->label('Net Amount')
                            ->money('EGP'),
                    ])->columns(3),
                Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Updated')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Transaction #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('instructor.name')
                    ->label('Instructor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sale' => 'success',
                        'payout' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->limit(25)
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform_fee')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('net_amount')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'cleared' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'sale' => 'Sale',
                        'payout' => 'Payout',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'cleared' => 'Cleared',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('instructor_id')
                    ->label('Instructor')
                    ->relationship('instructor', 'name', fn(Builder $query) => $query->where('role', 'instructor'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn(Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn(Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('markCompleted')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(InstructorTransaction $record): bool => $record->type === 'payout' && $record->status !== 'completed')
                    ->action(function (InstructorTransaction $record): void {
                        $record->update(['status' => 'completed']);
                        Notification::make()
                            ->title('Transaction marked as completed')
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
            'index' => ListInstructorTransactions::route('/'),
            'view' => ViewInstructorTransaction::route('/{record}'),
        ];
    }
}
