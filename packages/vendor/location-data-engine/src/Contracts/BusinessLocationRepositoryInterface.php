<?php

namespace Vendor\LocationDataEngine\Contracts;

use Vendor\LocationDataEngine\DTO\PlaceDetailsData;
use Vendor\LocationDataEngine\Models\BusinessLocation;
use Vendor\LocationDataEngine\Models\ScanSession;

interface BusinessLocationRepositoryInterface
{
    public function upsertFromPlaceDetails(ScanSession $session, PlaceDetailsData $details): BusinessLocation;

    public function touchScan(BusinessLocation $location, ScanSession $session): void;
}
