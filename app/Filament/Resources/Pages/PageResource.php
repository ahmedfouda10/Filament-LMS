<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages as PagePages;
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
            'index' => PagePages\ListPages::route('/'),
            'faq' => PagePages\ManageFaq::route('/faq'),
            'terms' => PagePages\ManageTerms::route('/terms'),
            'privacy' => PagePages\ManagePrivacy::route('/privacy'),
            'about' => PagePages\ManageAbout::route('/about'),
        ];
    }
}
