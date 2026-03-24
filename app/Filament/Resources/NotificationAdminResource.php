<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationAdminResource\Pages;
use App\Models\Notification;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationAdminResource extends Resource
{
    protected static ?string $model = Notification::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bell';
    protected static \UnitEnum|string|null $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Notifications';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('user_id')->label('User')->searchable()->preload()
                ->options(User::pluck('name', 'id'))->required(),
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\Textarea::make('body')->required(),
            Forms\Components\Select::make('type')->required()
                ->options(['enrollment' => 'Enrollment', 'payment' => 'Payment', 'certificate' => 'Certificate', 'quiz' => 'Quiz', 'subscription' => 'Subscription', 'system' => 'System']),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable(),
                Tables\Columns\TextColumn::make('title')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\IconColumn::make('read_at')->label('Read')
                    ->boolean()->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn ($record) => $record->read_at !== null),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['enrollment' => 'Enrollment', 'payment' => 'Payment', 'certificate' => 'Certificate', 'quiz' => 'Quiz', 'subscription' => 'Subscription', 'system' => 'System']),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationAdmins::route('/'),
            'create' => Pages\CreateNotificationAdmin::route('/create'),
        ];
    }
}
