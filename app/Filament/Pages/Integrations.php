<?php

namespace App\Filament\Pages;

use App\Integrations\Contracts\Integration;
use App\Integrations\IntegrationRegistry;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Integrations extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationLabel = 'Integrations';
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.pages.integrations';

    public function getIntegrations(): array
    {
        return IntegrationRegistry::all();
    }

    public function getCategories(): array
    {
        $integrations = IntegrationRegistry::all();
        $grouped = [];

        foreach ($integrations as $integration) {
            $category = $integration->category();
            $grouped[$category][] = $integration;
        }

        return $grouped;
    }

    public function setActiveGateway(string $slug): void
    {
        $integration = IntegrationRegistry::find($slug);
        if (!$integration || !$integration->isConnected()) {
            return;
        }

        Setting::set('active_payment_gateway', $slug);

        Notification::make()
            ->title($integration->name() . ' is now the active payment gateway')
            ->success()
            ->send();
    }

    public function configureAction(): Action
    {
        return Action::make('configure')
            ->fillForm(function (array $arguments): array {
                $integration = IntegrationRegistry::find($arguments['slug']);
                if (!$integration) {
                    return [];
                }

                $data = [];
                foreach ($integration->settingsKeys() as $key) {
                    $data[$key] = Setting::get($key) ?? '';
                }

                return $data;
            })
            ->form(function (array $arguments): array {
                $integration = IntegrationRegistry::find($arguments['slug']);
                if (!$integration) {
                    return [];
                }

                return $integration->formSchema();
            })
            ->modalHeading(function (array $arguments): string {
                $integration = IntegrationRegistry::find($arguments['slug']);
                return $integration ? 'Configure ' . $integration->name() : 'Configure Integration';
            })
            ->modalWidth('2xl')
            ->modalSubmitActionLabel('Save Configuration')
            ->action(function (array $data, array $arguments): void {
                $integration = IntegrationRegistry::find($arguments['slug']);
                if (!$integration) {
                    return;
                }

                foreach ($integration->settingsKeys() as $key) {
                    if (array_key_exists($key, $data)) {
                        Setting::set($key, $data[$key] ?? '');
                    }
                }

                Notification::make()
                    ->title($integration->name() . ' configuration saved')
                    ->success()
                    ->send();
            });
    }
}
