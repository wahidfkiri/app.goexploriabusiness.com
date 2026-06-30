<?php

namespace Vendor\LocationDataEngine\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Vendor\LocationDataEngine\Contracts\PlacesClientInterface;

class GooglePlacesClient implements PlacesClientInterface
{
    public function __construct(protected GoogleApiKeyPool $keyPool)
    {
    }

    public function textSearch(string $query, ?string $pageToken = null): array
    {
        $params = ['query' => $query];

        if ($pageToken) {
            sleep(2);
            $params['pagetoken'] = $pageToken;
        }

        return $this->request('textsearch/json', $params);
    }

    public function nearbySearch(float $latitude, float $longitude, int $radius, string $keyword, ?string $pageToken = null, ?string $type = null): array
    {
        $params = [
            'location' => sprintf('%s,%s', $latitude, $longitude),
            'radius' => $radius,
            'keyword' => $keyword,
        ];

        if ($type) {
            $params['type'] = $type;
        }

        if ($pageToken) {
            sleep(2);
            $params = ['pagetoken' => $pageToken];
        }

        return $this->request('nearbysearch/json', $params);
    }

    public function placeDetails(string $placeId): array
    {
        return $this->request('details/json', [
            'place_id' => $placeId,
            'fields' => implode(',', (array) config('location-data-engine.google.field_mask', [])),
        ]);
    }

    public function photoUrl(string $photoReference, int $maxWidth = 1600): string
    {
        $base = rtrim((string) config('location-data-engine.google.base_url'), '/');

        return sprintf(
            '%s/photo?photo_reference=%s&maxwidth=%d&key=%s',
            $base,
            urlencode($photoReference),
            $maxWidth,
            urlencode($this->keyPool->current())
        );
    }

    protected function request(string $endpoint, array $params): array
    {
        $attempts = max(1, (int) config('location-data-engine.google.retry', 3));
        $lastMessage = 'Google Places request failed.';

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $key = $attempt === 1 ? $this->keyPool->current() : $this->keyPool->rotate();
            $payload = array_merge($params, ['key' => $key]);

            try {
                $response = $this->client()->get(rtrim((string) config('location-data-engine.google.base_url'), '/') . '/' . ltrim($endpoint, '/'), $payload);
                $response->throw();

                $json = $response->json();
                $status = data_get($json, 'status', 'UNKNOWN');

                if (in_array($status, ['OK', 'ZERO_RESULTS'], true)) {
                    return $json;
                }

                if (in_array($status, ['OVER_QUERY_LIMIT', 'RESOURCE_EXHAUSTED'], true)) {
                    $lastMessage = 'Google Places quota exceeded.';
                    continue;
                }

                if ($status === 'INVALID_REQUEST' && isset($payload['pagetoken'])) {
                    sleep(2);
                    continue;
                }

                $lastMessage = (string) data_get($json, 'error_message', $status);
            } catch (RequestException $exception) {
                $lastMessage = $exception->getMessage();
            }
        }

        throw new RuntimeException($lastMessage);
    }

    protected function client(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout((int) config('location-data-engine.google.timeout', 20))
            ->retry(1, 250, throw: false);
    }
}
