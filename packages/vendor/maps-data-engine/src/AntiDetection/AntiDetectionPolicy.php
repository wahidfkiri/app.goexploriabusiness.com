<?php

namespace Vendor\MapsDataEngine\AntiDetection;

class AntiDetectionPolicy
{
    public function fingerprintSeed(): array
    {
        return [
            'viewport' => $this->randomViewport(),
            'timezone' => $this->randomTimezone(),
            'locale' => $this->randomLocale(),
            'user_agent_family' => $this->randomUserAgentHint(),
        ];
    }

    public function humanDelays(): array
    {
        return [
            'min' => (int) config('maps-data-engine.runtime.slowdown_ms_min', 800),
            'max' => (int) config('maps-data-engine.runtime.slowdown_ms_max', 2500),
        ];
    }

    protected function randomViewport(): array
    {
        $choices = [
            ['width' => 1366, 'height' => 768],
            ['width' => 1440, 'height' => 900],
            ['width' => 1536, 'height' => 864],
            ['width' => 1600, 'height' => 900],
        ];

        return $choices[array_rand($choices)];
    }

    protected function randomTimezone(): string
    {
        $choices = ['America/Toronto', 'America/Montreal', 'America/New_York', 'America/Vancouver'];

        return $choices[array_rand($choices)];
    }

    protected function randomLocale(): string
    {
        $choices = ['fr-CA', 'en-CA', 'en-US'];

        return $choices[array_rand($choices)];
    }

    protected function randomUserAgentHint(): string
    {
        $choices = ['chrome-windows', 'chrome-mac', 'edge-windows'];

        return $choices[array_rand($choices)];
    }
}
