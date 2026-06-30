<?php

namespace Vendor\LocationDataEngine\Models\Concerns;

trait UsesLocationDataEngineConnection
{
    public function getConnectionName(): ?string
    {
        return config('location-data-engine.database.connection');
    }
}
