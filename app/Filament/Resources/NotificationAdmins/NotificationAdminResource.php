<?php

namespace App\Filament\Resources\NotificationAdmins;

use App\Filament\Resources\NotificationAdmins\Pages;
use App\Filament\Resources\NotificationAdmins\Schemas\FormSchema;
use App\Filament\Resources\NotificationAdmins\Tables\NotificationAdminTable;
use App\Models\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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
        return FormSchema::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationAdminTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationAdmins::route('/'),
            'create' => Pages\CreateNotificationAdmin::route('/create'),
        ];
    }
}
