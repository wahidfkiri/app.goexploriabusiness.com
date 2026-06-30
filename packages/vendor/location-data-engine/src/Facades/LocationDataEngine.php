<?php

namespace Vendor\LocationDataEngine\Facades;

use Illuminate\Support\Facades\Facade;

class LocationDataEngine extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'location-data-engine';
    }
}
