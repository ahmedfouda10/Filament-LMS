<?php

namespace App\Filament\Resources\Certificates\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class CertificatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('certificate_number')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')->searchable()->sortable()->limit(30),
                Tables\Columns\TextColumn::make('issued_at')
                    ->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')
                    ->relationship('course', 'title')
                    ->label('Course')->searchable()->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
