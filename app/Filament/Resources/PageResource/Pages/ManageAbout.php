<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Setting;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;

class ManageAbout extends Page
{
    protected static string $resource = PageResource::class;
    protected static ?string $title = 'About Page';

    public function getView(): string
    {
        return 'filament.resources.page-resource.pages.manage-about';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'about_title' => Setting::get('about_title') ?? 'About SPC Online Academy',
            'about_description' => Setting::get('about_description') ?? '',
            'about_mission' => Setting::get('about_mission') ?? '',
            'about_vision' => Setting::get('about_vision') ?? '',
            'about_values' => json_decode(Setting::get('about_values') ?? '[]', true),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('about_title')
                    ->label('Page Title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('about_description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('about_mission')
                    ->label('Our Mission')
                    ->rows(4),
                Forms\Components\Textarea::make('about_vision')
                    ->label('Our Vision')
                    ->rows(4),
                Forms\Components\Repeater::make('about_values')
                    ->label('Core Values')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->required(),
                        Forms\Components\Select::make('icon')
                            ->label('Icon')
                            ->options([
                                'book' => 'Book',
                                'users' => 'Users',
                                'target' => 'Target',
                                'heart' => 'Heart',
                                'star' => 'Star',
                                'shield' => 'Shield',
                                'globe' => 'Globe',
                                'lightbulb' => 'Lightbulb',
                            ])
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->defaultItems(1)
                    ->addActionLabel('Add Value')
                    ->collapsible(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('about_title', $data['about_title']);
        Setting::set('about_description', $data['about_description']);
        Setting::set('about_mission', $data['about_mission']);
        Setting::set('about_vision', $data['about_vision']);
        Setting::set('about_values', json_encode($data['about_values']));
        Setting::set('about_updated', now()->format('F Y'));

        Notification::make()->title('About page saved successfully.')->success()->send();
    }
}
