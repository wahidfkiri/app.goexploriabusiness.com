<?php

namespace Vendor\MapMarker\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MapMarkerGeoSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $now = Carbon::now();
            $continentIds = $this->seedContinents($now);

            foreach ($this->dataset() as $countryData) {
                $countryId = $this->upsertCountry($countryData, $continentIds[$countryData['continent_code']], $now);

                foreach ($countryData['provinces'] as $province) {
                    $provinceId = $this->upsertProvince($countryId, $province, $now);
                    $regionId = $this->upsertRegion($provinceId, $countryData['code'], $province, $now);

                    foreach ($province['places'] as $index => $place) {
                        $secteurId = $this->upsertSecteur($regionId, $countryData['code'], $province['code'], $index, $place, $now);
                        $this->upsertVille($countryId, $provinceId, $regionId, $secteurId, $province, $index, $place, $now);

                        $mapPointId = $this->upsertMapPoint($countryData, $province, $place, $now);
                        $this->upsertMapPointDetails($mapPointId, $countryData, $province, $place, $now);
                        $this->replaceMapPointImages($mapPointId, $countryData, $province, $place, $now);
                        $this->replaceMapPointVideos($mapPointId, $place, $now);
                    }
                }
            }

            DB::commit();
            $this->command?->info('MapMarkerGeoSeeder: geo + map points seeded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function seedContinents(Carbon $now): array
    {
        DB::table('continents')->updateOrInsert(
            ['code' => 'NA'],
            [
                'name' => 'North America',
                'description' => 'North American continent',
                'is_active' => 1,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        DB::table('continents')->updateOrInsert(
            ['code' => 'EU'],
            [
                'name' => 'Europe',
                'description' => 'European continent',
                'is_active' => 1,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        return [
            'NA' => (int) DB::table('continents')->where('code', 'NA')->value('id'),
            'EU' => (int) DB::table('continents')->where('code', 'EU')->value('id'),
        ];
    }

    private function upsertCountry(array $countryData, int $continentId, Carbon $now): int
    {
        $payload = [
            'name' => $countryData['name'],
            'iso2' => $countryData['iso2'],
            'phone_code' => $countryData['phone_code'],
            'capital' => $countryData['capital'],
            'currency' => $countryData['currency'],
            'currency_symbol' => $countryData['currency_symbol'],
            'latitude' => (string) $countryData['latitude'],
            'longitude' => (string) $countryData['longitude'],
            'description' => $countryData['description'],
            'population' => $countryData['population'],
            'area' => $countryData['area'],
            'official_language' => $countryData['official_language'],
            'timezones' => json_encode($countryData['timezones'], JSON_UNESCAPED_UNICODE),
            'region' => $countryData['region_name'],
            'continent_id' => $continentId,
            'is_active' => 1,
            'updated_at' => $now,
        ];

        $existing = DB::table('countries')
            ->select('id', 'code', 'iso2')
            ->where('code', $countryData['code'])
            ->orWhere('iso2', $countryData['iso2'])
            ->first();

        if ($existing) {
            DB::table('countries')
                ->where('id', $existing->id)
                ->update($payload);

            return (int) $existing->id;
        }

        DB::table('countries')->insert(array_merge($payload, [
            'code' => $countryData['code'],
            'created_at' => $now,
        ]));

        return (int) DB::table('countries')
            ->where('code', $countryData['code'])
            ->value('id');
    }

    private function upsertProvince(int $countryId, array $province, Carbon $now): int
    {
        DB::table('provinces')->updateOrInsert(
            ['code' => $province['code'], 'country_id' => $countryId],
            [
                'name' => $province['name'],
                'capital' => $province['capital'],
                'largest_city' => $province['largest_city'],
                'official_language' => $province['official_language'] ?? null,
                'population' => $province['population'] ?? null,
                'area' => $province['area'] ?? null,
                'timezone' => $province['timezone'] ?? null,
                'description' => $province['description'] ?? ($province['name'] . ' administrative area'),
                'latitude' => isset($province['latitude']) ? (string) $province['latitude'] : null,
                'longitude' => isset($province['longitude']) ? (string) $province['longitude'] : null,
                'is_active' => 1,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        return (int) DB::table('provinces')
            ->where('code', $province['code'])
            ->where('country_id', $countryId)
            ->value('id');
    }

    private function upsertRegion(int $provinceId, string $countryCode, array $province, Carbon $now): int
    {
        $regionCode = substr($countryCode . '-' . $province['code'], 0, 10);

        DB::table('regions')->updateOrInsert(
            ['code' => $regionCode, 'province_id' => $provinceId],
            [
                'name' => $province['name'] . ' Region',
                'capital' => $province['capital'],
                'largest_city' => $province['largest_city'],
                'classification' => 'Administrative Region',
                'population' => $province['population'] ?? null,
                'area' => $province['area'] ?? null,
                'timezone' => $province['timezone'] ?? null,
                'description' => 'Regional area for ' . $province['name'],
                'latitude' => isset($province['latitude']) ? (string) $province['latitude'] : null,
                'longitude' => isset($province['longitude']) ? (string) $province['longitude'] : null,
                'is_active' => 1,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        return (int) DB::table('regions')
            ->where('code', $regionCode)
            ->where('province_id', $provinceId)
            ->value('id');
    }

    private function upsertSecteur(int $regionId, string $countryCode, string $provinceCode, int $index, array $place, Carbon $now): int
    {
        $secteurCode = substr(strtoupper($countryCode . $provinceCode . ($index + 1)), 0, 10);

        DB::table('secteurs')->updateOrInsert(
            ['code' => $secteurCode, 'region_id' => $regionId],
            [
                'name' => $place['name'] . ' Central District',
                'classification' => 'District',
                'description' => 'Central district of ' . $place['name'],
                'latitude' => (string) $place['lat'],
                'longitude' => (string) $place['lng'],
                'is_active' => 1,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        return (int) DB::table('secteurs')
            ->where('code', $secteurCode)
            ->where('region_id', $regionId)
            ->value('id');
    }

    private function upsertVille(
        int $countryId,
        int $provinceId,
        int $regionId,
        int $secteurId,
        array $province,
        int $index,
        array $place,
        Carbon $now
    ): void {
        $villeCode = substr(strtoupper($province['code'] . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)), 0, 10);

        DB::table('villes')->updateOrInsert(
            ['code' => $villeCode, 'province_id' => $provinceId, 'country_id' => $countryId],
            [
                'name' => $place['name'],
                'classification' => 'City',
                'status' => 'Major city',
                'population' => $place['population'] ?? null,
                'postal_code_prefix' => $place['postal'] ?? null,
                'latitude' => (string) $place['lat'],
                'longitude' => (string) $place['lng'],
                'description' => $place['name'] . ', ' . $province['name'],
                'attractions' => $place['landmark'],
                'is_active' => 1,
                'secteur_id' => $secteurId,
                'region_id' => $regionId,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );
    }

    private function upsertMapPoint(array $country, array $province, array $place, Carbon $now): int
    {
        $title = $place['landmark'] . ' - ' . $place['name'];
        $slug = Str::slug($country['code'] . '-' . $province['code'] . '-' . $place['name'] . '-' . $place['landmark']);

        DB::table('map_points')->updateOrInsert(
            ['title' => $title, 'latitude' => $place['lat'], 'longitude' => $place['lng']],
            [
                'description' => $place['description'],
                'category' => $place['category'] ?? 'tourism',
                'type' => 'point',
                'main_image' => 'seed/map-marker/' . strtolower($country['code']) . '/' . strtolower($province['code']) . '/' . $slug . '-main.jpg',
                'youtube_url' => $place['youtube_url'],
                'youtube_id' => $this->extractYoutubeId($place['youtube_url']),
                'adresse' => $place['address'],
                'ville' => $place['name'],
                'code_postal' => $place['postal'] ?? null,
                'details_url' => '/map-marker/points/' . $slug,
                'has_details_page' => 1,
                'etablissement_id' => null,
                'user_id' => null,
                'is_active' => 1,
                'is_featured' => 1,
                'views' => $place['views'] ?? 0,
                'deleted_at' => null,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        return (int) DB::table('map_points')
            ->where('title', $title)
            ->where('latitude', $place['lat'])
            ->where('longitude', $place['lng'])
            ->value('id');
    }

    private function upsertMapPointDetails(int $mapPointId, array $country, array $province, array $place, Carbon $now): void
    {
        $slug = Str::slug($country['code'] . '-' . $province['code'] . '-' . $place['name'] . '-' . $place['landmark']);

        DB::table('map_point_details')->updateOrInsert(
            ['map_point_id' => $mapPointId],
            [
                'long_description' => $place['long_description'] ?? ($place['description'] . ' Popular destination in ' . $place['name'] . '.'),
                'phone' => $place['phone'] ?? null,
                'email' => 'contact@' . Str::slug($place['name']) . '.example',
                'website' => $place['website'] ?? ('https://www.' . Str::slug($place['name']) . '.travel'),
                'horaires' => json_encode([
                    'monday' => '09:00-18:00',
                    'tuesday' => '09:00-18:00',
                    'wednesday' => '09:00-18:00',
                    'thursday' => '09:00-18:00',
                    'friday' => '09:00-19:00',
                    'saturday' => '10:00-19:00',
                    'sunday' => '10:00-17:00',
                ], JSON_UNESCAPED_UNICODE),
                'services' => json_encode(['guided_tour', 'ticketing', 'family_friendly'], JSON_UNESCAPED_UNICODE),
                'tarifs' => json_encode([
                    ['label' => 'Adult', 'price' => 25],
                    ['label' => 'Child', 'price' => 12],
                ], JSON_UNESCAPED_UNICODE),
                'contact_person' => 'Visitor Desk',
                'facebook' => 'https://facebook.com/' . Str::slug($place['name']),
                'instagram' => 'https://instagram.com/' . Str::slug($place['name']),
                'twitter' => 'https://x.com/' . Str::slug($place['name']),
                'linkedin' => 'https://linkedin.com/company/' . Str::slug($place['name']),
                'youtube' => $place['youtube_url'],
                'tripadvisor' => 'https://www.tripadvisor.com/',
                'yelp' => 'https://www.yelp.com/',
                'google_maps' => 'https://maps.google.com/?q=' . $place['lat'] . ',' . $place['lng'],
                'rating' => $place['rating'] ?? 4.5,
                'reviews_count' => $place['reviews_count'] ?? 100,
                'meta_title' => $place['landmark'] . ' - ' . $place['name'],
                'meta_description' => $place['description'],
                'slug' => $slug,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );
    }

    private function replaceMapPointImages(int $mapPointId, array $country, array $province, array $place, Carbon $now): void
    {
        DB::table('map_point_images')->where('map_point_id', $mapPointId)->delete();

        $baseSlug = Str::slug($country['code'] . '-' . $province['code'] . '-' . $place['name'] . '-' . $place['landmark']);
        $basePath = 'seed/map-marker/' . strtolower($country['code']) . '/' . strtolower($province['code']) . '/';

        $images = [
            [
                'image' => $basePath . $baseSlug . '-main.jpg',
                'thumbnail' => $basePath . $baseSlug . '-main-thumb.jpg',
                'caption' => $place['landmark'],
                'sort_order' => 1,
                'is_main' => 1,
            ],
            [
                'image' => $basePath . $baseSlug . '-city.jpg',
                'thumbnail' => $basePath . $baseSlug . '-city-thumb.jpg',
                'caption' => $place['name'] . ' city view',
                'sort_order' => 2,
                'is_main' => 0,
            ],
        ];

        foreach ($images as $image) {
            DB::table('map_point_images')->insert([
                'map_point_id' => $mapPointId,
                'image' => $image['image'],
                'thumbnail' => $image['thumbnail'],
                'caption' => $image['caption'],
                'sort_order' => $image['sort_order'],
                'is_main' => $image['is_main'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function replaceMapPointVideos(int $mapPointId, array $place, Carbon $now): void
    {
        DB::table('map_point_videos')->where('map_point_id', $mapPointId)->delete();

        $youtubeId = $this->extractYoutubeId($place['youtube_url']);
        if (!$youtubeId) {
            return;
        }

        DB::table('map_point_videos')->insert([
            'map_point_id' => $mapPointId,
            'title' => $place['landmark'] . ' travel video',
            'youtube_url' => $place['youtube_url'],
            'youtube_id' => $youtubeId,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function extractYoutubeId(string $url): ?string
    {
        if (preg_match('/(?:youtube\\.com\\/(?:[^\\/]+\\/.+\\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=)|youtu\\.be\\/)([^\"&?\\/\\s]{11})/i', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function dataset(): array
    {
        $youtube = 'https://www.youtube.com/watch?v=yERalriZLFM';

        return [
            [
                'continent_code' => 'NA',
                'code' => 'CAN',
                'iso2' => 'CA',
                'name' => 'Canada',
                'capital' => 'Ottawa',
                'phone_code' => '+1',
                'currency' => 'Canadian Dollar',
                'currency_symbol' => '$',
                'latitude' => 56.1304,
                'longitude' => -106.3468,
                'description' => 'Canada country data for map marker package',
                'population' => 41000000,
                'area' => 9984670,
                'official_language' => 'English, French',
                'timezones' => ['UTC-08:00', 'UTC-07:00', 'UTC-06:00', 'UTC-05:00', 'UTC-04:00', 'UTC-03:30'],
                'region_name' => 'North America',
                'provinces' => [
                    $this->province('ON', 'Ontario', 'Toronto', 51.2538, -85.3232, [
                        $this->place('Toronto', 'CN Tower', 43.6426, -79.3871, '301 Front St W, Toronto', 'M5V', 'culture', $youtube),
                        $this->place('Ottawa', 'Parliament Hill', 45.4236, -75.7009, 'Wellington St, Ottawa', 'K1A', 'history', $youtube),
                        $this->place('Niagara Falls', 'Horseshoe Falls', 43.0799, -79.0747, 'Niagara Pkwy, Niagara Falls', 'L2E', 'nature', $youtube),
                    ]),
                    $this->province('QC', 'Quebec', 'Quebec City', 52.9399, -73.5491, [
                        $this->place('Montreal', 'Old Montreal', 45.5075, -73.5540, 'Vieux-Montreal, Montreal', 'H2Y', 'culture', $youtube),
                        $this->place('Quebec City', 'Chateau Frontenac', 46.8119, -71.2065, '1 Rue des Carrieres, Quebec City', 'G1R', 'history', $youtube),
                        $this->place('Mont-Tremblant', 'Tremblant Resort', 46.2120, -74.5845, '1000 Chemin des Voyageurs, Mont-Tremblant', 'J8E', 'nature', $youtube),
                    ]),
                    $this->province('BC', 'British Columbia', 'Victoria', 53.7267, -127.6476, [
                        $this->place('Vancouver', 'Stanley Park', 49.3043, -123.1443, 'Stanley Park, Vancouver', 'V6G', 'nature', $youtube),
                        $this->place('Victoria', 'Butchart Gardens', 48.5651, -123.4695, '800 Benvenuto Ave, Brentwood Bay', 'V8M', 'nature', $youtube),
                        $this->place('Whistler', 'Whistler Village', 50.1163, -122.9574, 'Whistler Village, Whistler', 'V8E', 'adventure', $youtube),
                    ]),
                    $this->province('AB', 'Alberta', 'Edmonton', 53.9333, -116.5765, [
                        $this->place('Calgary', 'Calgary Tower', 51.0447, -114.0719, '101 9 Ave SW, Calgary', 'T2P', 'culture', $youtube),
                        $this->place('Edmonton', 'West Edmonton Mall', 53.5222, -113.6249, '8882 170 St NW, Edmonton', 'T5T', 'shopping', $youtube),
                        $this->place('Banff', 'Banff National Park', 51.4968, -115.9281, 'Banff National Park', 'T1L', 'nature', $youtube),
                    ]),
                    $this->province('MB', 'Manitoba', 'Winnipeg', 53.7609, -98.8139, [
                        $this->place('Winnipeg', 'The Forks', 49.8880, -97.1300, 'The Forks, Winnipeg', 'R3C', 'culture', $youtube),
                        $this->place('Churchill', 'Polar Bear Point', 58.7684, -94.1650, 'Churchill, Manitoba', 'R0B', 'nature', $youtube),
                        $this->place('Brandon', 'Riverbank Discovery Centre', 49.8389, -99.9501, '545 Conservation Dr, Brandon', 'R7A', 'nature', $youtube),
                    ]),
                    $this->province('SK', 'Saskatchewan', 'Regina', 52.9399, -106.4509, [
                        $this->place('Regina', 'Wascana Centre', 50.4452, -104.6165, 'Wascana Centre, Regina', 'S4S', 'nature', $youtube),
                        $this->place('Saskatoon', 'Meewasin Valley', 52.1332, -106.6700, 'Meewasin Trail, Saskatoon', 'S7K', 'nature', $youtube),
                        $this->place('Prince Albert', 'Prince Albert National Park', 53.2033, -105.7531, 'Prince Albert National Park', 'S6V', 'nature', $youtube),
                    ]),
                    $this->province('NS', 'Nova Scotia', 'Halifax', 44.6819, -63.7443, [
                        $this->place('Halifax', 'Halifax Waterfront', 44.6488, -63.5752, 'Lower Water St, Halifax', 'B3H', 'culture', $youtube),
                        $this->place('Peggys Cove', 'Peggys Point Lighthouse', 44.4939, -63.9152, 'Peggys Cove, NS', 'B3Z', 'nature', $youtube),
                        $this->place('Sydney', 'Cabot Trail Gateway', 46.1368, -60.1942, 'Sydney, Cape Breton', 'B1P', 'adventure', $youtube),
                    ]),
                    $this->province('NB', 'New Brunswick', 'Fredericton', 46.5653, -66.4619, [
                        $this->place('Fredericton', 'Beaverbrook Art Gallery', 45.9627, -66.6410, '703 Queen St, Fredericton', 'E3B', 'culture', $youtube),
                        $this->place('Moncton', 'Magnetic Hill', 46.1164, -64.8339, '2840 Mountain Rd, Moncton', 'E1G', 'adventure', $youtube),
                        $this->place('Saint John', 'Reversing Falls', 45.2733, -66.0838, 'Reversing Falls, Saint John', 'E2M', 'nature', $youtube),
                    ]),
                    $this->province('NL', 'Newfoundland and Labrador', 'St Johns', 53.1355, -57.6604, [
                        $this->place('St Johns', 'Signal Hill', 47.5708, -52.6810, 'Signal Hill Rd, St Johns', 'A1A', 'history', $youtube),
                        $this->place('Rocky Harbour', 'Gros Morne National Park', 49.6467, -57.8060, 'Gros Morne National Park', 'A0K', 'nature', $youtube),
                        $this->place('Corner Brook', 'Marble Mountain', 48.9342, -57.9228, 'Marble Dr, Corner Brook', 'A2H', 'adventure', $youtube),
                    ]),
                    $this->province('PE', 'Prince Edward Island', 'Charlottetown', 46.5107, -63.4168, [
                        $this->place('Charlottetown', 'Confederation Centre', 46.2361, -63.1261, '145 Richmond St, Charlottetown', 'C1A', 'history', $youtube),
                        $this->place('Cavendish', 'Green Gables Heritage Place', 46.4956, -63.3813, '8619 Cavendish Rd, Cavendish', 'C0A', 'culture', $youtube),
                        $this->place('Summerside', 'Spinnakers Landing', 46.3920, -63.7902, 'Spinnakers Landing, Summerside', 'C1N', 'culture', $youtube),
                    ]),
                    $this->province('YT', 'Yukon', 'Whitehorse', 64.2823, -135.0000, [
                        $this->place('Whitehorse', 'Miles Canyon', 60.6885, -135.0470, 'Miles Canyon Rd, Whitehorse', 'Y1A', 'nature', $youtube),
                        $this->place('Dawson City', 'Klondike Historic Site', 64.0601, -139.4322, 'Dawson City, Yukon', 'Y0B', 'history', $youtube),
                        $this->place('Haines Junction', 'Kluane Gateway', 60.7523, -137.5108, 'Haines Junction, Yukon', 'Y0B', 'nature', $youtube),
                    ]),
                    $this->province('NT', 'Northwest Territories', 'Yellowknife', 64.8255, -124.8457, [
                        $this->place('Yellowknife', 'Prince of Wales Heritage Centre', 62.4540, -114.3718, 'Yellowknife, NT', 'X1A', 'history', $youtube),
                        $this->place('Inuvik', 'Inuvik Igloo Church', 68.3602, -133.7230, 'Inuvik, NT', 'X0E', 'culture', $youtube),
                        $this->place('Hay River', 'Great Slave Lake Viewpoint', 60.8156, -115.7999, 'Hay River, NT', 'X0E', 'nature', $youtube),
                    ]),
                    $this->province('NU', 'Nunavut', 'Iqaluit', 70.2998, -83.1076, [
                        $this->place('Iqaluit', 'Unikkaarvik Visitor Centre', 63.7467, -68.5170, 'Iqaluit, NU', 'X0A', 'culture', $youtube),
                        $this->place('Pond Inlet', 'Baffin Island Gateway', 72.6976, -77.9635, 'Pond Inlet, NU', 'X0A', 'nature', $youtube),
                        $this->place('Rankin Inlet', 'Kivalliq Heritage', 62.8114, -92.0853, 'Rankin Inlet, NU', 'X0C', 'culture', $youtube),
                    ]),
                ],
            ],
            [
                'continent_code' => 'NA',
                'code' => 'USA',
                'iso2' => 'US',
                'name' => 'United States',
                'capital' => 'Washington',
                'phone_code' => '+1',
                'currency' => 'US Dollar',
                'currency_symbol' => '$',
                'latitude' => 37.0902,
                'longitude' => -95.7129,
                'description' => 'United States data for map marker package',
                'population' => 340000000,
                'area' => 9833517,
                'official_language' => 'English',
                'timezones' => ['UTC-10:00', 'UTC-09:00', 'UTC-08:00', 'UTC-07:00', 'UTC-06:00', 'UTC-05:00'],
                'region_name' => 'North America',
                'provinces' => [
                    $this->province('CA', 'California', 'Sacramento', 36.7783, -119.4179, [
                        $this->place('Los Angeles', 'Griffith Observatory', 34.1184, -118.3004, '2800 E Observatory Rd, Los Angeles', '90027', 'culture', $youtube),
                        $this->place('San Francisco', 'Golden Gate Bridge', 37.8199, -122.4783, 'Golden Gate Bridge, San Francisco', '94129', 'history', $youtube),
                        $this->place('San Diego', 'Balboa Park', 32.7341, -117.1446, 'Balboa Park, San Diego', '92101', 'nature', $youtube),
                    ]),
                    $this->province('NY', 'New York', 'Albany', 43.0000, -75.0000, [
                        $this->place('New York', 'Statue of Liberty', 40.6892, -74.0445, 'Liberty Island, New York', '10004', 'history', $youtube),
                        $this->place('Buffalo', 'Niagara Falls State Park', 43.0962, -79.0377, 'Niagara Falls State Park, NY', '14303', 'nature', $youtube),
                        $this->place('Albany', 'Empire State Plaza', 42.6508, -73.7593, 'Empire State Plaza, Albany', '12210', 'culture', $youtube),
                    ]),
                    $this->province('TX', 'Texas', 'Austin', 31.0000, -100.0000, [
                        $this->place('Austin', 'Texas State Capitol', 30.2747, -97.7404, '1100 Congress Ave, Austin', '78701', 'history', $youtube),
                        $this->place('Houston', 'Space Center Houston', 29.5502, -95.0970, '1601 E NASA Pkwy, Houston', '77058', 'science', $youtube),
                        $this->place('San Antonio', 'The Alamo', 29.4259, -98.4861, '300 Alamo Plaza, San Antonio', '78205', 'history', $youtube),
                    ]),
                ],
            ],
            [
                'continent_code' => 'NA',
                'code' => 'MEX',
                'iso2' => 'MX',
                'name' => 'Mexico',
                'capital' => 'Mexico City',
                'phone_code' => '+52',
                'currency' => 'Mexican Peso',
                'currency_symbol' => '$',
                'latitude' => 23.6345,
                'longitude' => -102.5528,
                'description' => 'Mexico data for map marker package',
                'population' => 129000000,
                'area' => 1964375,
                'official_language' => 'Spanish',
                'timezones' => ['UTC-08:00', 'UTC-07:00', 'UTC-06:00'],
                'region_name' => 'North America',
                'provinces' => [
                    $this->province('CM', 'Ciudad de Mexico', 'Mexico City', 19.4326, -99.1332, [
                        $this->place('Mexico City', 'Zocalo', 19.4327, -99.1332, 'Plaza de la Constitucion, CDMX', '06000', 'history', $youtube),
                        $this->place('Mexico City', 'Chapultepec Castle', 19.4204, -99.1819, 'Bosque de Chapultepec, CDMX', '11100', 'history', $youtube),
                        $this->place('Xochimilco', 'Trajineras Embarcadero', 19.2840, -99.1046, 'Xochimilco, CDMX', '16090', 'culture', $youtube),
                    ]),
                    $this->province('JA', 'Jalisco', 'Guadalajara', 20.6597, -103.3496, [
                        $this->place('Guadalajara', 'Hospicio Cabanas', 20.6761, -103.3390, 'Cabanas 8, Guadalajara', '44360', 'culture', $youtube),
                        $this->place('Puerto Vallarta', 'Malecon', 20.6110, -105.2343, 'Malecon, Puerto Vallarta', '48300', 'beach', $youtube),
                        $this->place('Tequila', 'Agave Landscape', 20.8820, -103.8358, 'Tequila, Jalisco', '46400', 'culture', $youtube),
                    ]),
                    $this->province('NL', 'Nuevo Leon', 'Monterrey', 25.6866, -100.3161, [
                        $this->place('Monterrey', 'Macroplaza', 25.6714, -100.3090, 'Macroplaza, Monterrey', '64000', 'culture', $youtube),
                        $this->place('Santa Catarina', 'La Huasteca', 25.6430, -100.4500, 'La Huasteca, Santa Catarina', '66350', 'nature', $youtube),
                        $this->place('Santiago', 'Cola de Caballo', 25.4239, -100.1520, 'Cola de Caballo, Santiago', '67300', 'nature', $youtube),
                    ]),
                ],
            ],
            [
                'continent_code' => 'EU',
                'code' => 'FRA',
                'iso2' => 'FR',
                'name' => 'France',
                'capital' => 'Paris',
                'phone_code' => '+33',
                'currency' => 'Euro',
                'currency_symbol' => 'EUR',
                'latitude' => 46.2276,
                'longitude' => 2.2137,
                'description' => 'France data for map marker package',
                'population' => 68000000,
                'area' => 551695,
                'official_language' => 'French',
                'timezones' => ['UTC+01:00'],
                'region_name' => 'Europe',
                'provinces' => [
                    $this->province('IF', 'Ile-de-France', 'Paris', 48.8566, 2.3522, [
                        $this->place('Paris', 'Eiffel Tower', 48.8584, 2.2945, 'Champ de Mars, Paris', '75007', 'history', $youtube),
                        $this->place('Versailles', 'Palace of Versailles', 48.8049, 2.1204, 'Place d Armes, Versailles', '78000', 'history', $youtube),
                        $this->place('Marne-la-Vallee', 'Disneyland Paris', 48.8674, 2.7830, 'Boulevard de Parc, Chessy', '77700', 'family', $youtube),
                    ]),
                    $this->province('PA', 'Provence-Alpes-Cote d Azur', 'Marseille', 43.9352, 6.0679, [
                        $this->place('Marseille', 'Old Port', 43.2965, 5.3698, 'Vieux Port, Marseille', '13002', 'culture', $youtube),
                        $this->place('Nice', 'Promenade des Anglais', 43.6950, 7.2656, 'Promenade des Anglais, Nice', '06000', 'beach', $youtube),
                        $this->place('Avignon', 'Palais des Papes', 43.9519, 4.8077, 'Place du Palais, Avignon', '84000', 'history', $youtube),
                    ]),
                    $this->province('AR', 'Auvergne-Rhone-Alpes', 'Lyon', 45.7640, 4.8357, [
                        $this->place('Lyon', 'Basilica of Notre-Dame de Fourviere', 45.7622, 4.8224, '8 Place de Fourviere, Lyon', '69005', 'history', $youtube),
                        $this->place('Annecy', 'Lake Annecy', 45.8992, 6.1294, 'Lake Annecy, Annecy', '74000', 'nature', $youtube),
                        $this->place('Chamonix', 'Aiguille du Midi', 45.8786, 6.8878, 'Chamonix-Mont-Blanc', '74400', 'adventure', $youtube),
                    ]),
                ],
            ],
        ];
    }

    private function province(string $code, string $name, string $capital, float $lat, float $lng, array $places): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'capital' => $capital,
            'largest_city' => $capital,
            'official_language' => null,
            'population' => null,
            'area' => null,
            'timezone' => null,
            'latitude' => $lat,
            'longitude' => $lng,
            'places' => $places,
        ];
    }

    private function place(
        string $city,
        string $landmark,
        float $lat,
        float $lng,
        string $address,
        ?string $postal,
        string $category,
        string $youtubeUrl
    ): array {
        return [
            'name' => $city,
            'landmark' => $landmark,
            'lat' => $lat,
            'lng' => $lng,
            'address' => $address,
            'postal' => $postal,
            'category' => $category,
            'youtube_url' => $youtubeUrl,
            'description' => $landmark . ' in ' . $city,
            'long_description' => $landmark . ' is a well known place in ' . $city . ' frequently visited by travelers.',
            'rating' => 4.6,
            'reviews_count' => 250,
            'views' => 0,
        ];
    }
}
