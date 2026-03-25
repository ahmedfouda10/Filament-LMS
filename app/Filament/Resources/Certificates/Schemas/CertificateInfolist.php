<?php

namespace App\Filament\Resources\Certificates\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CertificateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
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
}
