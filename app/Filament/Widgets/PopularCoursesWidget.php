<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularCoursesWidget extends BaseWidget
{
    protected static ?string $heading = 'Popular Courses';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Course::query()->withCount('enrollments')->orderByDesc('enrollments_count')->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->limit(40),
                Tables\Columns\TextColumn::make('instructor.name')
                    ->label('Instructor'),
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label('Students')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('EGP'),
            ])
            ->paginated(false);
    }
}
