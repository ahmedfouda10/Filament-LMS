<?php

namespace App\Filament\Resources\InstructorTransactions\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InfolistSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
}
