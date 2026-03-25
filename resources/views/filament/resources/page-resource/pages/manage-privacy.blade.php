<x-filament-panels::page>
    <form wire:submit="save">
        <x-filament::section
            icon="heroicon-o-shield-check"
            icon-color="success"
            description="Write your Privacy Policy content below. HTML formatting is supported via the rich text editor."
        >
            {{ $this->form }}

            <x-slot name="footer">
                <x-filament::button type="submit" icon="heroicon-o-check" size="lg">
                    Save Privacy Policy
                </x-filament::button>
            </x-slot>
        </x-filament::section>
    </form>
</x-filament-panels::page>
