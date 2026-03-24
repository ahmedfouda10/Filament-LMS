<?php

namespace App\Filament\Resources\NotificationAdmins\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class NotificationAdminTable
{
    public static function configure(Table $table): Table
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
}
