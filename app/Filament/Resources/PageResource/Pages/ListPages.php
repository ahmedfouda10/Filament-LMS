<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Faq;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class ListPages extends Page
{
    protected static string $resource = PageResource::class;
    protected static ?string $title = 'Pages';

    public function getView(): string
    {
        return 'filament.resources.page-resource.pages.list-pages';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('about')
                ->label('About Page')
                ->icon('heroicon-o-information-circle')
                ->url(PageResource::getUrl('about')),
            Action::make('faq')
                ->label('Manage FAQ (' . Faq::count() . ')')
                ->icon('heroicon-o-question-mark-circle')
                ->url(PageResource::getUrl('faq'))
                ->color('gray'),
            Action::make('terms')
                ->label('Terms of Service')
                ->icon('heroicon-o-document-text')
                ->url(PageResource::getUrl('terms'))
                ->color('gray'),
            Action::make('privacy')
                ->label('Privacy Policy')
                ->icon('heroicon-o-shield-check')
                ->url(PageResource::getUrl('privacy'))
                ->color('gray')
        ];
    }

    protected function getViewData(): array
    {
        return [
            'faqCount' => Faq::count(),
            'termsUpdated' => Setting::get('page_terms_updated') ?? 'Never',
            'privacyUpdated' => Setting::get('page_privacy_updated') ?? 'Never',
            'aboutUpdated' => Setting::get('about_updated') ?? 'Never',
        ];
    }
}
