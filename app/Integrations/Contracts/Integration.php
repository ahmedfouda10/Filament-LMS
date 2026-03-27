<?php

namespace App\Integrations\Contracts;

use Filament\Forms\Components\Component;

interface Integration
{
    public function slug(): string;

    public function name(): string;

    public function description(): string;

    public function icon(): string;

    public function category(): string;

    /** @return array<string> */
    public function settingsKeys(): array;

    /** @return array<string> Required keys to be considered "connected" */
    public function requiredKeys(): array;

    /** @return array<Component> Filament form components */
    public function formSchema(): array;

    public function isConnected(): bool;
}
