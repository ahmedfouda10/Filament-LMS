<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\Pages\CreateCourse;
use App\Filament\Resources\CourseResource\Pages\EditCourse;
use App\Filament\Resources\CourseResource\Pages\ListCourses;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Filament\Resources\CourseResource\RelationManagers\EnrollmentsRelationManager;
use App\Filament\Resources\CourseResource\RelationManagers\ModulesRelationManager;
use App\Filament\Resources\CourseResource\RelationManagers\ReviewsRelationManager;
use App\Models\Course;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static \UnitEnum|string|null $navigationGroup = 'Courses';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Image')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('instructor.name')
                    ->label('Instructor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Students')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_published'),
                Tables\Columns\ToggleColumn::make('is_featured'),
                Tables\Columns\TextColumn::make('is_bundle')
                    ->badge()
                    ->label('Bundle')
                    ->formatStateUsing(fn(bool $state) => $state ? 'Bundle' : 'Single')
                    ->colors([
                        'primary' => true,
                        'gray' => false,
                    ]),
                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->colors([
                        'success' => 'beginner',
                        'warning' => 'intermediate',
                        'danger' => 'advanced',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->preload(),
                Tables\Filters\SelectFilter::make('level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ]),
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\TernaryFilter::make('is_bundle'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ModulesRelationManager::class,
            ReviewsRelationManager::class,
            EnrollmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }
}
