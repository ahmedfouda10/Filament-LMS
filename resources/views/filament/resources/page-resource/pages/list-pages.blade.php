<x-filament-panels::page>
    <x-filament::section icon="heroicon-o-information-circle" icon-color="primary">
        <x-slot name="heading">
            <a href="{{ \App\Filament\Resources\Pages\PageResource::getUrl('about') }}" class="hover:underline">About Page</a>
        </x-slot>
        <x-slot name="description">Edit the About page content (title, mission, vision, values).</x-slot>
        <x-slot name="headerEnd">
            <x-filament::badge color="info">{{ $aboutUpdated }}</x-filament::badge>
        </x-slot>
        <x-filament::button :href="\App\Filament\Resources\Pages\PageResource::getUrl('about')" tag="a" icon="heroicon-m-arrow-right" icon-position="after"
            size="sm">
            Edit About Page
        </x-filament::button>
    </x-filament::section>
    <x-filament::section icon="heroicon-o-question-mark-circle" icon-color="primary">
        <x-slot name="heading">
            <a href="{{ \App\Filament\Resources\Pages\PageResource::getUrl('faq') }}" class="hover:underline">FAQ</a>
        </x-slot>
        <x-slot name="description">Manage frequently asked questions grouped by sections.</x-slot>
        <x-slot name="headerEnd">
            <x-filament::badge color="primary">{{ $faqCount }} questions</x-filament::badge>
        </x-slot>
        <x-filament::button :href="\App\Filament\Resources\Pages\PageResource::getUrl('faq')" tag="a" icon="heroicon-m-arrow-right" icon-position="after"
            size="sm">
            Manage FAQ
        </x-filament::button>
    </x-filament::section>

    <x-filament::section icon="heroicon-o-document-text" icon-color="primary">
        <x-slot name="heading">
            <a href="{{ \App\Filament\Resources\Pages\PageResource::getUrl('terms') }}" class="hover:underline">Terms of
                Service</a>
        </x-slot>
        <x-slot name="description">Edit the Terms of Service page content.</x-slot>
        <x-slot name="headerEnd">
            <x-filament::badge color="warning">{{ $termsUpdated }}</x-filament::badge>
        </x-slot>
        <x-filament::button :href="\App\Filament\Resources\Pages\PageResource::getUrl('terms')" tag="a" icon="heroicon-m-arrow-right" icon-position="after"
            size="sm">
            Edit Terms
        </x-filament::button>
    </x-filament::section>

    <x-filament::section icon="heroicon-o-shield-check" icon-color="primary">
        <x-slot name="heading">
            <a href="{{ \App\Filament\Resources\Pages\PageResource::getUrl('privacy') }}" class="hover:underline">Privacy
                Policy</a>
        </x-slot>
        <x-slot name="description">Edit the Privacy Policy page content.</x-slot>
        <x-slot name="headerEnd">
            <x-filament::badge color="success">{{ $privacyUpdated }}</x-filament::badge>
        </x-slot>
        <x-filament::button :href="\App\Filament\Resources\Pages\PageResource::getUrl('privacy')" tag="a" icon="heroicon-m-arrow-right" icon-position="after"
            size="sm">
            Edit Privacy Policy
        </x-filament::button>
    </x-filament::section>

</x-filament-panels::page>
