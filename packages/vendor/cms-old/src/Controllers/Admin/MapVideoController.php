<?php

namespace Vendor\Cms\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use App\Models\MapPoint;
use App\Models\MapPointDetail;
use App\Models\MapPointImage;
use App\Models\MapPointVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MapVideoController extends Controller
{
    public function index($etablissementId): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);

            $points = MapPoint::with(['videos', 'details', 'images'])
                ->where('etablissement_id', $etablissement->id)
                ->orderByDesc('id')
                ->get()
                ->map(fn (MapPoint $point) => $this->mapPointPayload($point))
                ->values();

            return response()->json([
                'success' => true,
                'data' => $points,
                'stats' => [
                    'total' => $points->count(),
                    'active' => $points->where('is_active', true)->count(),
                    'with_video' => $points->where('has_video', true)->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('CMS map videos index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des points maps',
            ], 500);
        }
    }

    public function store(Request $request, $etablissementId): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $data = $this->validatedData($request);
            $youtubeId = $this->extractYoutubeId($data['youtube_url']);

            if (!$youtubeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL YouTube invalide. Utilisez une URL YouTube, Shorts ou youtu.be.',
                    'errors' => ['youtube_url' => ['URL YouTube invalide.']],
                ], 422);
            }

            $point = MapPoint::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? 'video_map',
                'type' => 'point',
                'youtube_url' => $data['youtube_url'],
                'youtube_id' => $youtubeId,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'adresse' => $data['adresse'] ?? null,
                'ville' => $data['ville'] ?? null,
                'code_postal' => $data['code_postal'] ?? null,
                'details_url' => $data['details_url'] ?? null,
                'has_details_page' => $request->boolean('has_details_page') || !empty($data['details_url']),
                'etablissement_id' => $etablissement->id,
                'user_id' => Auth::id(),
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured', false),
                'views' => 0,
                'main_image' => $this->storeMainImage($request),
            ]);

            $this->syncPrimaryVideo($point, $data['youtube_url'], $youtubeId, $data['video_title'] ?? $data['title']);
            $this->syncDetails($point, $data['details'] ?? []);
            $this->syncGalleryImages($request, $point);

            return response()->json([
                'success' => true,
                'message' => 'Point vidéo map créé avec succès',
                'data' => $this->mapPointPayload($point->fresh(['videos', 'details', 'images'])),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('CMS map videos store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $etablissementId, $id): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $point = MapPoint::where('etablissement_id', $etablissement->id)->findOrFail($id);
            $data = $this->validatedData($request);
            $youtubeId = $this->extractYoutubeId($data['youtube_url']);

            if (!$youtubeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL YouTube invalide. Utilisez une URL YouTube, Shorts ou youtu.be.',
                    'errors' => ['youtube_url' => ['URL YouTube invalide.']],
                ], 422);
            }

            $pointData = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? 'video_map',
                'youtube_url' => $data['youtube_url'],
                'youtube_id' => $youtubeId,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'adresse' => $data['adresse'] ?? null,
                'ville' => $data['ville'] ?? null,
                'code_postal' => $data['code_postal'] ?? null,
                'details_url' => $data['details_url'] ?? null,
                'has_details_page' => $request->boolean('has_details_page') || !empty($data['details_url']),
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured', false),
            ];

            if ($request->hasFile('main_image')) {
                if ($point->main_image) {
                    $this->deleteStoredPublicFile($point->main_image);
                }

                $pointData['main_image'] = $this->storeMainImage($request);
            }

            $point->update($pointData);

            $this->syncPrimaryVideo($point, $data['youtube_url'], $youtubeId, $data['video_title'] ?? $data['title']);
            $this->syncDetails($point, $data['details'] ?? []);
            $this->syncGalleryImages($request, $point);

            return response()->json([
                'success' => true,
                'message' => 'Point vidéo map mis à jour avec succès',
                'data' => $this->mapPointPayload($point->fresh(['videos', 'details', 'images'])),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('CMS map videos update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($etablissementId, $id): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $point = MapPoint::where('etablissement_id', $etablissement->id)->findOrFail($id);
            $point->delete();

            return response()->json([
                'success' => true,
                'message' => 'Point vidéo map supprimé avec succès',
            ]);
        } catch (\Throwable $e) {
            Log::error('CMS map videos delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
            ], 500);
        }
    }

    public function toggle($etablissementId, $id): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $point = MapPoint::where('etablissement_id', $etablissement->id)->findOrFail($id);
            $point->update(['is_active' => !$point->is_active]);

            return response()->json([
                'success' => true,
                'message' => $point->fresh()->is_active ? 'Point activé' : 'Point désactivé',
                'data' => $this->mapPointPayload($point->fresh(['videos', 'details', 'images'])),
            ]);
        } catch (\Throwable $e) {
            Log::error('CMS map videos toggle error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut',
            ], 500);
        }
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:191',
            'video_title' => 'nullable|string|max:191',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'youtube_url' => 'required|url|max:191',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'adresse' => 'nullable|string|max:500',
            'ville' => 'nullable|string|max:191',
            'code_postal' => 'nullable|string|max:20',
            'details_url' => 'nullable|string|max:191',
            'has_details_page' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'main_image' => 'nullable|image|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'nullable|image|max:2048',
            'details.long_description' => 'nullable|string',
            'details.phone' => 'nullable|string|max:50',
            'details.email' => 'nullable|email|max:191',
            'details.website' => 'nullable|url|max:191',
            'details.contact_person' => 'nullable|string|max:191',
            'details.rating' => 'nullable|numeric|min:0|max:5',
            'details.reviews_count' => 'nullable|integer|min:0',
            'details.facebook' => 'nullable|url|max:191',
            'details.instagram' => 'nullable|url|max:191',
            'details.twitter' => 'nullable|url|max:191',
            'details.linkedin' => 'nullable|url|max:191',
            'details.youtube' => 'nullable|url|max:191',
            'details.tiktok' => 'nullable|url|max:191',
            'details.pinterest' => 'nullable|url|max:191',
            'details.snapchat' => 'nullable|string|max:191',
            'details.whatsapp' => 'nullable|string|max:50',
            'details.telegram' => 'nullable|string|max:191',
            'details.discord' => 'nullable|string|max:191',
            'details.twitch' => 'nullable|url|max:191',
            'details.reddit' => 'nullable|url|max:191',
            'details.github' => 'nullable|url|max:191',
            'details.medium' => 'nullable|url|max:191',
            'details.tumblr' => 'nullable|url|max:191',
            'details.vimeo' => 'nullable|url|max:191',
            'details.dribbble' => 'nullable|url|max:191',
            'details.behance' => 'nullable|url|max:191',
            'details.soundcloud' => 'nullable|url|max:191',
            'details.spotify' => 'nullable|url|max:191',
            'details.tripadvisor' => 'nullable|url|max:191',
            'details.foursquare' => 'nullable|url|max:191',
            'details.yelp' => 'nullable|url|max:191',
            'details.google_maps' => 'nullable|url|max:191',
        ]);
    }

    private function syncPrimaryVideo(MapPoint $point, string $youtubeUrl, string $youtubeId, ?string $title): void
    {
        MapPointVideo::updateOrCreate(
            ['map_point_id' => $point->id, 'sort_order' => 1],
            [
                'title' => $title ?: $point->title,
                'youtube_url' => $youtubeUrl,
                'youtube_id' => $youtubeId,
            ]
        );
    }

    private function syncDetails(MapPoint $point, array $details): void
    {
        $fields = [
            'long_description',
            'phone',
            'email',
            'website',
            'contact_person',
            'rating',
            'reviews_count',
            'facebook',
            'instagram',
            'twitter',
            'linkedin',
            'youtube',
            'tiktok',
            'pinterest',
            'snapchat',
            'whatsapp',
            'telegram',
            'discord',
            'twitch',
            'reddit',
            'github',
            'medium',
            'tumblr',
            'vimeo',
            'dribbble',
            'behance',
            'soundcloud',
            'spotify',
            'tripadvisor',
            'foursquare',
            'yelp',
            'google_maps',
        ];

        $payload = collect($fields)
            ->mapWithKeys(function ($field) use ($details) {
                $value = $details[$field] ?? null;
                $value = is_string($value) ? trim($value) : $value;

                return [$field => $value === '' ? null : $value];
            })
            ->all();

        $payload['rating'] = $payload['rating'] !== null ? (float) $payload['rating'] : 0;
        $payload['reviews_count'] = $payload['reviews_count'] !== null ? (int) $payload['reviews_count'] : 0;

        $hasContent = collect($payload)
            ->except(['rating', 'reviews_count'])
            ->contains(fn ($value) => $value !== null && $value !== '')
            || $payload['rating'] > 0
            || $payload['reviews_count'] > 0;

        if (!$hasContent && !$point->details()->exists()) {
            return;
        }

        MapPointDetail::updateOrCreate(
            ['map_point_id' => $point->id],
            $payload
        );
    }

    private function storeMainImage(Request $request): ?string
    {
        if (!$request->hasFile('main_image')) {
            return null;
        }

        $path = $request->file('main_image')->store('map-points/' . date('Y/m/d'), 'public');

        return $this->absoluteStorageUrl($request, $path);
    }

    private function syncGalleryImages(Request $request, MapPoint $point): void
    {
        if (!$request->hasFile('additional_images')) {
            return;
        }

        $maxOrder = MapPointImage::where('map_point_id', $point->id)->max('sort_order') ?? 0;

        foreach ($request->file('additional_images') as $index => $image) {
            if (!$image->isValid()) {
                continue;
            }

            $imagePath = $image->store('map-points/gallery/' . date('Y/m/d'), 'public');
            $imageUrl = $this->absoluteStorageUrl($request, $imagePath);

            MapPointImage::create([
                'map_point_id' => $point->id,
                'image' => $imageUrl,
                'thumbnail' => $imageUrl,
                'caption' => '',
                'sort_order' => $maxOrder + $index + 1,
                'is_main' => false,
            ]);
        }
    }

    private function absoluteStorageUrl(Request $request, string $path): string
    {
        $url = Storage::url($path);

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return rtrim($request->getSchemeAndHttpHost(), '/') . '/' . ltrim($url, '/');
    }

    private function deleteStoredPublicFile(?string $pathOrUrl): void
    {
        $path = $this->publicStoragePath($pathOrUrl);

        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function publicStoragePath(?string $pathOrUrl): ?string
    {
        $value = trim((string) $pathOrUrl);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
            $storagePrefix = '/storage/';
            $position = strpos($path, $storagePrefix);

            return $position === false ? null : ltrim(substr($path, $position + strlen($storagePrefix)), '/');
        }

        if (Str::startsWith($value, ['/storage/', 'storage/'])) {
            return ltrim(Str::after($value, 'storage/'), '/');
        }

        return ltrim($value, '/');
    }

    private function mapPointPayload(MapPoint $point): array
    {
        $point->loadMissing(['videos', 'details', 'images']);

        $video = $point->videos->first();
        $youtubeId = $point->youtube_id ?: ($video->youtube_id ?? null);

        return [
            'id' => $point->id,
            'title' => $point->title,
            'video_title' => $video->title ?? $point->title,
            'description' => $point->description,
            'category' => $point->category,
            'youtube_url' => $point->youtube_url ?: ($video->youtube_url ?? ''),
            'youtube_id' => $youtubeId,
            'thumbnail' => $youtubeId ? "https://img.youtube.com/vi/{$youtubeId}/mqdefault.jpg" : null,
            'main_image' => $point->main_image,
            'main_image_url' => $this->publicFileUrl($point->main_image),
            'images' => $point->images->map(fn (MapPointImage $image) => [
                'id' => $image->id,
                'image' => $image->image,
                'url' => $this->publicFileUrl($image->image),
                'thumbnail' => $this->publicFileUrl($image->thumbnail ?: $image->image),
                'caption' => $image->caption,
                'sort_order' => $image->sort_order,
            ])->values(),
            'latitude' => (float) $point->latitude,
            'longitude' => (float) $point->longitude,
            'adresse' => $point->adresse,
            'ville' => $point->ville,
            'code_postal' => $point->code_postal,
            'details_url' => $point->details_url,
            'has_details_page' => (bool) $point->has_details_page,
            'details' => $point->details ? [
                'long_description' => $point->details->long_description,
                'phone' => $point->details->phone,
                'email' => $point->details->email,
                'website' => $point->details->website,
                'contact_person' => $point->details->contact_person,
                'rating' => $point->details->rating,
                'reviews_count' => $point->details->reviews_count,
                'facebook' => $point->details->facebook,
                'instagram' => $point->details->instagram,
                'twitter' => $point->details->twitter,
                'linkedin' => $point->details->linkedin,
                'youtube' => $point->details->youtube,
                'tiktok' => $point->details->tiktok,
                'pinterest' => $point->details->pinterest,
                'snapchat' => $point->details->snapchat,
                'whatsapp' => $point->details->whatsapp,
                'telegram' => $point->details->telegram,
                'discord' => $point->details->discord,
                'twitch' => $point->details->twitch,
                'reddit' => $point->details->reddit,
                'github' => $point->details->github,
                'medium' => $point->details->medium,
                'tumblr' => $point->details->tumblr,
                'vimeo' => $point->details->vimeo,
                'dribbble' => $point->details->dribbble,
                'behance' => $point->details->behance,
                'soundcloud' => $point->details->soundcloud,
                'spotify' => $point->details->spotify,
                'tripadvisor' => $point->details->tripadvisor,
                'foursquare' => $point->details->foursquare,
                'yelp' => $point->details->yelp,
                'google_maps' => $point->details->google_maps,
            ] : null,
            'is_active' => (bool) $point->is_active,
            'is_featured' => (bool) $point->is_featured,
            'has_video' => !empty($youtubeId),
            'created_at' => optional($point->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function publicFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['/storage/', 'storage/'])) {
            return url('/' . ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    private function extractYoutubeId(?string $url): ?string
    {
        $value = trim((string) $url);
        if ($value === '') {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?.*v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/', $value, $matches)) {
            return $matches[1];
        }

        return Str::length($value) === 11 ? $value : null;
    }
}
