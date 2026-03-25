<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Message Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('subject')
                            ->label('Subject')
                            ->badge(),
                        Infolists\Components\IconEntry::make('is_read')
                            ->label('Read')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Received At')
                            ->dateTime(),
                    ])->columns(2),
                Section::make('Message')
                    ->schema([
                        Infolists\Components\TextEntry::make('message')
                            ->label('')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
