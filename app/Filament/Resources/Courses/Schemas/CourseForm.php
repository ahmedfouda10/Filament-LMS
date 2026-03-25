<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Info')->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live()
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('instructor_id')
                    ->relationship('instructor', 'name', fn($query) => $query->where('role', 'instructor'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('language')
                    ->maxLength(50)
                    ->default('Arabic'),
            ])->columns(2),

            Section::make('Content')->schema([
                Forms\Components\Textarea::make('subtitle')
                    ->label('Short Description')
                    ->maxLength(500)
                    ->rows(3),
                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull(),
            ]),

            Section::make('Pricing')->schema([
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('EGP')
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('original_price')
                    ->numeric()
                    ->prefix('EGP')
                    ->minValue(0),
                Forms\Components\Toggle::make('is_bundle')
                    ->default(false),
            ])->columns(3),

            Section::make('Media')->schema([
                Forms\Components\FileUpload::make('thumbnail')
                    ->image()
                    ->directory('courses/thumbnails')
                    ->maxSize(5120)
                    ->label('Course Image'),
                Forms\Components\TextInput::make('preview_video_url')
                    ->url()
                    ->maxLength(500)
                    ->label('Preview Video URL'),
            ])->columns(2),

            Section::make('Details')->schema([
                Forms\Components\Repeater::make('requirements')
                    ->simple(
                        Forms\Components\TextInput::make('requirement')
                            ->required(),
                    )
                    ->defaultItems(1)
                    ->addActionLabel('Add Requirement')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('learning_outcomes')
                    ->simple(
                        Forms\Components\TextInput::make('outcome')
                            ->required(),
                    )
                    ->defaultItems(1)
                    ->addActionLabel('Add Learning Outcome')
                    ->columnSpanFull(),
                Forms\Components\TagsInput::make('tags')
                    ->columnSpanFull(),
            ]),

            Section::make('Visibility')->schema([
                Forms\Components\Toggle::make('is_published')
                    ->default(false),
                Forms\Components\Toggle::make('is_featured')
                    ->default(false),
                Forms\Components\TextInput::make('badge_text')
                    ->label('Badge Text')
                    ->placeholder('e.g. Most Popular, Hot Bundle')
                    ->maxLength(50),
                Forms\Components\Select::make('badge_color')
                    ->label('Badge Color')
                    ->options(['blue' => 'Blue', 'green' => 'Green', 'orange' => 'Orange', 'red' => 'Red', 'purple' => 'Purple'])
                    ->placeholder('Select color'),
            ])->columns(2),
        ]);
    }
}
