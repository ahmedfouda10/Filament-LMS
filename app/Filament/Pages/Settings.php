<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Settings extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?int $navigationSort = 100;
    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General Settings')->schema([
                    Forms\Components\TextInput::make('site_name')
                        ->label('Site Name')
                        ->required(),
                    Forms\Components\FileUpload::make('logo')
                        ->label('Logo')
                        ->image()
                        ->directory('settings'),
                    Forms\Components\TextInput::make('contact_phone')
                        ->label('Contact Phone'),
                    Forms\Components\TextInput::make('contact_email')
                        ->label('Contact Email')
                        ->email(),
                ])->columns(2),
                Section::make('Appearance')->schema([
                    Forms\Components\ColorPicker::make('primary_color')
                        ->label('Primary Color'),
                    Forms\Components\ColorPicker::make('secondary_color')
                        ->label('Secondary Color'),
                ])->columns(2),
                Section::make('Business Settings')->schema([
                    Forms\Components\TextInput::make('platform_fee_percentage')
                        ->label('Platform Fee (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100),
                    Forms\Components\TextInput::make('certificate_validity_years')
                        ->label('Certificate Validity (Years)')
                        ->numeric()
                        ->minValue(1),
                ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
