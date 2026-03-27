<?php

namespace App\Integrations;

use App\Integrations\Contracts\Integration;
use App\Integrations\Providers\PaymobIntegration;

class IntegrationRegistry
{
    /** @return array<Integration> */
    public static function all(): array
    {
        return [
            new PaymobIntegration(),
        ];
    }

    public static function find(string $slug): ?Integration
    {
        foreach (static::all() as $integration) {
            if ($integration->slug() === $slug) {
                return $integration;
            }
        }

        return null;
    }

    /** @return array<Integration> */
    public static function byCategory(string $category): array
    {
        return array_filter(static::all(), fn (Integration $i) => $i->category() === $category);
    }

    /** @return array<string> */
    public static function categories(): array
    {
        return array_unique(array_map(fn (Integration $i) => $i->category(), static::all()));
    }
}
