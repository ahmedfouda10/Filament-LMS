<x-filament-panels::page>
    <form wire:submit="save">
        <x-filament::section
            icon="heroicon-o-information-circle"
            icon-color="info"
            description="Manage the About page content shown on the public website."
        >
            {{ $this->form }}

            <x-slot name="footer">
                <x-filament::button type="submit" icon="heroicon-o-check" size="lg">
                    Save About Page
                </x-filament::button>
            </x-slot>
        </x-filament::section>
    </form>
</x-filament-panels::page>
