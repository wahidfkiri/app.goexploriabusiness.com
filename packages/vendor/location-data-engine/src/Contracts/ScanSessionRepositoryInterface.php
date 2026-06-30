<?php

namespace Vendor\LocationDataEngine\Contracts;

use Vendor\LocationDataEngine\DTO\ScanRequestData;
use Vendor\LocationDataEngine\Models\ScanSession;

interface ScanSessionRepositoryInterface
{
    public function createFromRequest(ScanRequestData $data): ScanSession;

    public function markRunning(ScanSession $session): ScanSession;

    public function updateProgress(ScanSession $session, array $payload): ScanSession;

    public function finish(ScanSession $session, array $payload = []): ScanSession;

    public function fail(ScanSession $session, string $message, array $context = []): ScanSession;
}
