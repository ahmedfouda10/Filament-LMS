<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quiz Details')->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive(),
                Forms\Components\Select::make('lesson_id')
                    ->relationship('lesson', 'title')
                    ->searchable()
                    ->preload()
                    ->label('Lesson (optional)'),
                Forms\Components\TextInput::make('passing_score')
                    ->numeric()
                    ->required()
                    ->default(70)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                Forms\Components\TextInput::make('time_limit_minutes')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('minutes')
                    ->label('Time Limit'),
                Forms\Components\TextInput::make('max_attempts')
                    ->numeric()
                    ->minValue(1)
                    ->default(3)
                    ->label('Max Attempts'),
            ])->columns(2),
        ]);
    }
}
