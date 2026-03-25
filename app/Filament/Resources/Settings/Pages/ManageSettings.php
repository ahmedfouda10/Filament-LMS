<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\Schemas\SettingForm;
use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;

class ManageSettings extends Page
{
    protected static string $resource = SettingResource::class;

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return SettingForm::configure($schema)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $toggleKeys = ['maintenance_mode', 'feature_social_login', 'feature_live_chat', 'feature_push_notifications', 'feature_offline_downloads', 'feature_messaging', 'installment_enabled', 'messaging_enabled', 'announcement_enabled'];

        foreach ($data as $key => $value) {
            if (in_array($key, $toggleKeys)) {
                $value = $value ? 'true' : 'false';
            }
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
