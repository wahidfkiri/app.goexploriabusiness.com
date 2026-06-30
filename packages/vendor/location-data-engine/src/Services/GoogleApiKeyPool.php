<?php

namespace Vendor\LocationDataEngine\Services;

use RuntimeException;

class GoogleApiKeyPool
{
    protected array $keys;

    protected int $index = 0;

    public function __construct()
    {
        $this->keys = array_values(array_filter((array) config('location-data-engine.google.api_keys', [])));

        if ($this->keys === []) {
            throw new RuntimeException('No Google Places API keys configured for location data engine.');
        }
    }

    public function current(): string
    {
        return $this->keys[$this->index] ?? $this->keys[0];
    }

    public function rotate(): string
    {
        $this->index = ($this->index + 1) % count($this->keys);

        return $this->current();
    }

    public function count(): int
    {
        return count($this->keys);
    }
}
