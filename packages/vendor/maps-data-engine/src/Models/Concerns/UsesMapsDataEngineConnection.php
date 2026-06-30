<?php

namespace Vendor\MapsDataEngine\Models\Concerns;

trait UsesMapsDataEngineConnection
{
    public function getConnectionName(): ?string
    {
        return config('maps-data-engine.database.connection');
    }
}
