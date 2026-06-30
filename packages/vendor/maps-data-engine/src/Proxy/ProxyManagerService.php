<?php

namespace Vendor\MapsDataEngine\Proxy;

use Illuminate\Support\Str;
use Vendor\MapsDataEngine\Models\MapProxyEndpoint;

class ProxyManagerService
{
    public function pick(?MapProxyEndpoint $exclude = null): ?MapProxyEndpoint
    {
        return MapProxyEndpoint::query()
            ->where('is_active', true)
            ->when($exclude, fn ($query) => $query->whereKeyNot($exclude->id))
            ->where(function ($query) {
                $query->whereNull('blacklisted_until')->orWhere('blacklisted_until', '<', now());
            })
            ->orderByDesc('health_score')
            ->orderBy('last_checked_at')
            ->first();
    }

    public function blacklist(MapProxyEndpoint $proxy, string $reason): void
    {
        $proxy->forceFill([
            'failure_count' => $proxy->failure_count + 1,
            'health_score' => max(0, $proxy->health_score - 15),
            'blacklisted_until' => now()->addSeconds((int) config('maps-data-engine.proxy.blacklist_ttl', 1800)),
            'last_checked_at' => now(),
            'meta' => array_merge((array) $proxy->meta, ['last_blacklist_reason' => $reason]),
        ])->save();
    }

    public function markHealthy(MapProxyEndpoint $proxy): void
    {
        $proxy->forceFill([
            'success_count' => $proxy->success_count + 1,
            'health_score' => min(100, $proxy->health_score + 2),
            'last_checked_at' => now(),
            'blacklisted_until' => null,
        ])->save();
    }

    public function formatForNode(?MapProxyEndpoint $proxy): ?array
    {
        if (! $proxy) {
            return null;
        }

        return [
            'id' => $proxy->id,
            'server' => sprintf('%s://%s:%d', $proxy->scheme, $proxy->host, $proxy->port),
            'username' => $proxy->username,
            'password' => $proxy->password,
        ];
    }
}
