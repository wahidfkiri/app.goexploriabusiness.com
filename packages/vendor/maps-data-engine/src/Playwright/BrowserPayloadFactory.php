<?php

namespace Vendor\MapsDataEngine\Playwright;

use Vendor\MapsDataEngine\AntiDetection\AntiDetectionPolicy;
use Vendor\MapsDataEngine\AntiDetection\HumanBehaviorProfile;
use Vendor\MapsDataEngine\DTO\ScrapeSegmentData;
use Vendor\MapsDataEngine\Models\MapBrowserSession;
use Vendor\MapsDataEngine\Models\MapProxyEndpoint;
use Vendor\MapsDataEngine\Models\MapScanSession;

class BrowserPayloadFactory
{
    public function __construct(
        protected AntiDetectionPolicy $antiDetectionPolicy,
        protected HumanBehaviorProfile $humanBehaviorProfile,
    ) {
    }

    public function build(MapScanSession $session, ScrapeSegmentData $segment, ?MapProxyEndpoint $proxy, ?MapBrowserSession $browserSession): array
    {
        return [
            'session' => [
                'id' => $session->id,
                'uuid' => $session->uuid,
                'category' => $session->category,
                'limit' => $session->limit,
                'radius' => $session->radius,
            ],
            'segment' => $segment->toArray(),
            'proxy' => $proxy ? [
                'id' => $proxy->id,
                'server' => sprintf('%s://%s:%d', $proxy->scheme, $proxy->host, $proxy->port),
                'username' => $proxy->username,
                'password' => $proxy->password,
            ] : null,
            'browser_session' => $browserSession ? [
                'id' => $browserSession->id,
                'session_key' => $browserSession->session_key,
                'storage_state_path' => $browserSession->storage_state_path,
                'fingerprint' => $browserSession->fingerprint,
            ] : null,
            'anti_detection' => $this->antiDetectionPolicy->fingerprintSeed(),
            'human_profile' => $this->humanBehaviorProfile->export(),
            'features' => [
                'with_images' => (bool) $session->with_images,
                'with_reviews' => (bool) $session->with_reviews,
                'with_social_links' => (bool) $session->with_social_links,
            ],
            'runtime' => [
                'screenshots_dir' => config('maps-data-engine.runtime.screenshots_directory'),
                'result_dir' => config('maps-data-engine.runtime.result_directory'),
                'sessions_dir' => config('maps-data-engine.runtime.sessions_directory'),
                'headless' => (bool) config('maps-data-engine.runtime.headless', true),
            ],
        ];
    }
}
