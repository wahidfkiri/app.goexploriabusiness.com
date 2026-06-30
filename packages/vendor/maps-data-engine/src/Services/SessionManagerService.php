<?php

namespace Vendor\MapsDataEngine\Services;

use Illuminate\Support\Str;
use Vendor\MapsDataEngine\AntiDetection\AntiDetectionPolicy;
use Vendor\MapsDataEngine\Models\MapBrowserSession;
use Vendor\MapsDataEngine\Models\MapProxyEndpoint;

class SessionManagerService
{
    public function __construct(protected AntiDetectionPolicy $antiDetectionPolicy)
    {
    }

    public function reserve(?MapProxyEndpoint $proxy = null): MapBrowserSession
    {
        $session = MapBrowserSession::query()
            ->when($proxy, fn ($query) => $query->where('proxy_endpoint_id', $proxy->id))
            ->where('is_locked', false)
            ->where('is_banned', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('last_used_at')
            ->first();

        if (! $session) {
            $sessionKey = 'mde-' . Str::uuid();
            $session = MapBrowserSession::query()->create([
                'proxy_endpoint_id' => $proxy?->id,
                'session_key' => $sessionKey,
                'storage_state_path' => $this->defaultStorageStatePath($sessionKey),
                'fingerprint' => $this->antiDetectionPolicy->fingerprintSeed(),
                'last_used_at' => now(),
                'expires_at' => now()->addDays(3),
                'is_locked' => false,
                'is_banned' => false,
            ]);
        }

        $session->forceFill([
            'proxy_endpoint_id' => $proxy?->id,
            'storage_state_path' => $session->storage_state_path ?: $this->defaultStorageStatePath($session->session_key),
            'is_locked' => true,
            'last_used_at' => now(),
        ])->save();

        return $session->fresh();
    }

    public function release(MapBrowserSession $session, ?array $storageState = null, ?string $storageStatePath = null): void
    {
        $payload = [
            'is_locked' => false,
            'last_used_at' => now(),
            'storage_state_path' => $storageStatePath ?: ($session->storage_state_path ?: $this->defaultStorageStatePath($session->session_key)),
        ];

        if ($storageState !== null) {
            $payload['storage_state'] = $storageState;
        }

        $session->forceFill($payload)->save();
    }

    public function markBanned(MapBrowserSession $session, string $reason): void
    {
        $session->forceFill([
            'is_locked' => false,
            'is_banned' => true,
            'expires_at' => now()->addMinutes((int) config('maps-data-engine.security.captcha_cooldown_minutes', 30)),
            'fingerprint' => array_merge((array) $session->fingerprint, ['ban_reason' => $reason]),
        ])->save();
    }

    protected function defaultStorageStatePath(string $sessionKey): string
    {
        return rtrim((string) config('maps-data-engine.runtime.sessions_directory'), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $sessionKey
            . '.json';
    }
}
