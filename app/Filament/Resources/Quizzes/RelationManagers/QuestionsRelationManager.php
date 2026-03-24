<?php

namespace App\Filament\Resources\Quizzes\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Textarea::make('question_text')
                ->required()
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('explanation')
                ->rows(2)
                ->columnSpanFull()
                ->helperText('Explanation shown after answering'),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            Forms\Components\Repeater::make('options')
                ->relationship()
                ->schema([
                    Forms\Components\TextInput::make('option_label')
                        ->required()
                        ->maxLength(5)
                        ->placeholder('A, B, C, D')
                        ->label('Label'),
                    Forms\Components\TextInput::make('option_text')
                        ->required()
                        ->maxLength(500)
                        ->label('Option Text'),
                    Forms\Components\Toggle::make('is_correct')
                        ->label('Correct Answer')
                        ->default(false),
                ])
                ->columns(3)
                ->defaultItems(4)
                ->addActionLabel('Add Option')
                ->columnSpanFull()
                ->reorderable(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('question_text')
                    ->label('Question')
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\TextColumn::make('explanation')
                    ->limit(40)
                    ->placeholder('No explanation'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('options_count')
                    ->counts('options')
                    ->label('Options'),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
