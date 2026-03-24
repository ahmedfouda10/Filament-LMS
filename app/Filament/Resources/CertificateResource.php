<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
use Filament\Actions\ViewAction;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-trophy';
    protected static \UnitEnum|string|null $navigationGroup = 'Learning';
    protected static ?int $navigationSort = 5;

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Certificate Details')->schema([
                Infolists\Components\TextEntry::make('certificate_number')->label('Certificate Number'),
                Infolists\Components\TextEntry::make('student_name')->label('Student Name'),
                Infolists\Components\TextEntry::make('user.email')->label('Student Email'),
                Infolists\Components\TextEntry::make('course.title')->label('Course'),
                Infolists\Components\TextEntry::make('issued_at')->dateTime()->label('Issued At'),
                Infolists\Components\TextEntry::make('valid_until')->dateTime()->label('Valid Until'),
                Infolists\Components\TextEntry::make('certificate_url')->label('Certificate URL'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificates::route('/'),
            'view' => Pages\ViewCertificate::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
