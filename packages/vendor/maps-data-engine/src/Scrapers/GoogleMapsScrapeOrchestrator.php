<?php

namespace Vendor\MapsDataEngine\Scrapers;

use Throwable;
use Vendor\MapsDataEngine\DTO\ScrapeSegmentData;
use Vendor\MapsDataEngine\Models\MapScanSession;
use Vendor\MapsDataEngine\Playwright\BrowserPayloadFactory;
use Vendor\MapsDataEngine\Playwright\PlaywrightRunnerService;
use Vendor\MapsDataEngine\Proxy\ProxyManagerService;
use Vendor\MapsDataEngine\Services\MapsScrapeLogService;
use Vendor\MapsDataEngine\Services\ResultStorageService;
use Vendor\MapsDataEngine\Services\SessionManagerService;

class GoogleMapsScrapeOrchestrator
{
    public function __construct(
        protected ProxyManagerService $proxyManager,
        protected SessionManagerService $sessionManager,
        protected BrowserPayloadFactory $payloadFactory,
        protected PlaywrightRunnerService $runner,
        protected ResultStorageService $storageService,
        protected MapsScrapeLogService $logService,
    ) {
    }

    public function scrape(MapScanSession $session, ScrapeSegmentData $segment): array
    {
        $proxy = $this->proxyManager->pick();
        $browserSession = $this->sessionManager->reserve($proxy);

        try {
            $payload = $this->payloadFactory->build($session, $segment, $proxy, $browserSession);
            $response = $this->runner->run($payload);

            if (($response['status'] ?? 'ok') === 'captcha') {
                if ($proxy) {
                    $this->proxyManager->blacklist($proxy, 'captcha-detected');
                }
                $this->sessionManager->markBanned($browserSession, 'captcha-detected');
                $this->logService->warning($session, 'captcha.detected', 'Captcha or suspicious activity detected.', [
                    'segment' => $segment->toArray(),
                    'proxy_id' => $proxy?->id,
                ]);

                return [
                    'status' => 'captcha',
                    'items' => [],
                    'meta' => $response,
                ];
            }

            if ($proxy) {
                $this->proxyManager->markHealthy($proxy);
            }

            $saved = $this->storageService->store($session, (array) ($response['items'] ?? []), [
                'country' => $segment->country,
                'province' => $segment->province,
                'region' => $segment->region,
                'city' => $segment->city,
                'business_status' => $response['business_status'] ?? null,
            ]);

            $this->sessionManager->release(
                $browserSession,
                $response['storage_state'] ?? null,
                $response['storage_state_path'] ?? null
            );
            $this->logService->info($session, 'segment.scraped', 'Segment scraped successfully.', [
                'segment' => $segment->toArray(),
                'items' => count($saved),
                'proxy_id' => $proxy?->id,
            ]);

            return [
                'status' => 'ok',
                'items' => $saved,
                'meta' => $response,
            ];
        } catch (Throwable $throwable) {
            if ($proxy) {
                $this->proxyManager->blacklist($proxy, $throwable->getMessage());
            }
            $this->sessionManager->release($browserSession);
            $this->logService->error($session, 'segment.failed', $throwable->getMessage(), [
                'segment' => $segment->toArray(),
                'proxy_id' => $proxy?->id,
                'exception' => get_class($throwable),
            ]);
            throw $throwable;
        }
    }
}
