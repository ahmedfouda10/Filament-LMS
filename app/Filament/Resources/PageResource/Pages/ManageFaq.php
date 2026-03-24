<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Faq;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;

class ManageFaq extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = PageResource::class;
    protected static ?string $title = 'FAQ';
    public function getView(): string
    {
        return 'filament.resources.page-resource.pages.manage-faq';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Faq::query())
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->label('Section')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('question')
                    ->searchable()
                    ->limit(80),
                Tables\Columns\TextColumn::make('answer')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Section')
                    ->options(fn () => Faq::distinct()->pluck('category', 'category')->toArray()),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(Faq::class)
                    ->form([
                        Forms\Components\TextInput::make('category')
                            ->label('Section')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g. Subscriptions'),
                        Forms\Components\TextInput::make('question')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('answer')
                            ->required()
                            ->rows(4),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('category')
                            ->label('Section')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('question')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('answer')
                            ->required()
                            ->rows(4),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric(),
                        Forms\Components\Toggle::make('is_active'),
                    ]),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('category')
            ->defaultGroup('category')
            ->reorderable('sort_order');
    }
}
