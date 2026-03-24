<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Faq;
use Filament\Resources\Resource;

class PageResource extends Resource
{
    protected static ?string $model = Faq::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-duplicate';
    protected static \UnitEnum|string|null $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Pages';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'pages';
    protected static ?string $breadcrumb = 'Pages';
    protected static ?string $modelLabel = 'Page';
    protected static ?string $pluralModelLabel = 'Pages';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'faq' => Pages\ManageFaq::route('/faq'),
            'terms' => Pages\ManageTerms::route('/terms'),
            'privacy' => Pages\ManagePrivacy::route('/privacy'),
            'about' => Pages\ManageAbout::route('/about'),
        ];
    }
}
