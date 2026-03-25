<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Models\Subscription;
use Filament\Forms;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
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
}
