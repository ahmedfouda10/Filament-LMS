<x-filament-panels::page>
    @php
        $activeGateway = \App\Models\Setting::get('active_payment_gateway');
    @endphp

    @foreach ($this->getCategories() as $category => $integrations)
        <x-filament::section :heading="$category" :description="count($integrations) . ' integration(s)'">
            @foreach ($integrations as $integration)
                @php
                    $isActive = $category === 'Payment Gateway' && $activeGateway === $integration->slug();
                    $isConnected = $integration->isConnected();
                @endphp

                <x-filament::section
                    :icon="$integration->icon()"
                    :icon-color="$isActive ? 'primary' : ($isConnected ? 'success' : 'gray')"
                    :heading="$integration->name()"
                    :description="$integration->description()"
                >
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        @if ($isActive)
                            <x-filament::badge color="primary" icon="heroicon-m-check-circle">Active Gateway</x-filament::badge>
                        @endif

                        @if ($isConnected)
                            <x-filament::badge color="success" icon="heroicon-m-signal">Connected</x-filament::badge>
                        @else
                            <x-filament::badge color="danger" icon="heroicon-m-minus-circle">Not Connected</x-filament::badge>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <x-filament::button
                            wire:click="mountAction('configure', { slug: '{{ $integration->slug() }}' })"
                            color="gray"
                            size="sm"
                            icon="heroicon-m-cog-6-tooth"
                        >
                            {{ $isConnected ? 'Settings' : 'Connect' }}
                        </x-filament::button>

                        @if ($isConnected && !$isActive && $category === 'Payment Gateway')
                            <x-filament::button
                                wire:click="setActiveGateway('{{ $integration->slug() }}')"
                                color="primary"
                                size="sm"
                                icon="heroicon-m-bolt"
                            >
                                Set Active
                            </x-filament::button>
                        @endif
                    </div>
                </x-filament::section>
            @endforeach
        </x-filament::section>
    @endforeach

    <x-filament-actions::modals />
</x-filament-panels::page>
