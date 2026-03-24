<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Setting;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;

class ManagePrivacy extends Page
{
    protected static string $resource = PageResource::class;
    protected static ?string $title = 'Privacy Policy';
    public function getView(): string
    {
        return 'filament.resources.page-resource.pages.manage-privacy';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'content' => Setting::get('page_privacy') ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\RichEditor::make('content')
                    ->label('Privacy Policy Content')
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
        Setting::set('page_privacy', $data['content']);
        Setting::set('page_privacy_updated', now()->format('F Y'));
        Notification::make()->title('Privacy Policy saved successfully.')->success()->send();
    }
}
