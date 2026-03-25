<x-filament-panels::page>
    <form wire:submit="save">
        <x-filament::section
            icon="heroicon-o-document-text"
            icon-color="warning"
            description="Write your Terms of Service content below. HTML formatting is supported via the rich text editor."
        >
            {{ $this->form }}

            <x-slot name="footer">
                <x-filament::button type="submit" icon="heroicon-o-check" size="lg">
                    Save Terms of Service
                </x-filament::button>
            </x-slot>
        </x-filament::section>
    </form>
</x-filament-panels::page>
