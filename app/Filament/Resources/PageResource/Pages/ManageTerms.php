<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Setting;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;

class ManageTerms extends Page
{
    protected static string $resource = PageResource::class;
    protected static ?string $title = 'Terms of Service';
    public function getView(): string
    {
        return 'filament.resources.page-resource.pages.manage-terms';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'content' => Setting::get('page_terms') ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\RichEditor::make('content')
                    ->label('Terms of Service Content')
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'h2',
                        'h3',
                        'bulletList',
                        'orderedList',
                        'link',
                        'blockquote',
                        'redo',
                        'undo',
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Setting::set('page_terms', $data['content']);
        Setting::set('page_terms_updated', now()->format('F Y'));
        Notification::make()->title('Terms of Service saved successfully.')->success()->send();
    }
}
