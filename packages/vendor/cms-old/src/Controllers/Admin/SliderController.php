<?php

namespace Vendor\Cms\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Vendor\Cms\Models\Media;
use Vendor\Cms\Models\Setting;
use Vendor\Cms\Services\MediaToSliderSyncService;

class SliderController extends Controller
{
    protected MediaToSliderSyncService $mediaToSliderSyncService;

    public function __construct()
    {
        $this->mediaToSliderSyncService = app(MediaToSliderSyncService::class);
    }

    /**
     * Get all slider items from settings + media tab.
     */
    public function index($etablissementId): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);

            $settingItems = collect(
                Setting::where('etablissement_id', $etablissement->id)
                ->where('group', 'slider')
                ->orderBy('order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(fn (Setting $setting) => $this->mapSettingSliderItem($setting))
                ->all()
            );

            $mediaItems = collect(
                Media::where('etablissement_id', $etablissement->id)
                ->where('is_slider', true)
                ->orderBy('order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(fn (Media $media) => $this->mapMediaSliderItem($media))
                ->all()
            );

            $sliderItems = $settingItems
                ->merge($mediaItems)
                ->sortBy([
                    ['order', 'asc'],
                    ['source', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();

            return response()->json([
                'success' => true,
                'data' => $sliderItems,
            ]);
        } catch (\Throwable $e) {
            Log::error('Slider index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du slider: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new slider item.
     */
    public function store(Request $request, $etablissementId): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);

            $request->validate([
                'type' => 'required|in:image,video',
                'media_id' => 'nullable|exists:cms.cms_media,id',
                'image_file' => 'nullable|file|image|max:5120',
                'video_file' => 'nullable|file|mimes:mp4,mov,ogg,webm|max:51200',
                'external_url' => 'nullable|url|max:2048',
                'video_url' => 'nullable|url|max:2048',
                'title' => 'nullable|string|max:255',
                'subtitle' => 'nullable|string|max:500',
                'button_text' => 'nullable|string|max:100',
                'button_link' => 'nullable|string|max:500',
                'title_size' => 'nullable|string|max:20',
                'title_color' => 'nullable|string|max:20',
                'title_font' => 'nullable|string|max:120',
                'description_size' => 'nullable|string|max:20',
                'description_color' => 'nullable|string|max:20',
                'description_font' => 'nullable|string|max:120',
                'button_size' => 'nullable|string|max:20',
                'button_color' => 'nullable|string|max:20',
                'button_bg_color' => 'nullable|string|max:20',
                'button_font' => 'nullable|string|max:120',
            ]);

            $order = $this->getNextSliderOrder($etablissement->id);

            $value = [
                'type' => $request->type,
                'title' => $request->title ?? '',
                'subtitle' => $request->subtitle ?? '',
                'button_text' => $request->button_text ?? '',
                'button_link' => $request->button_link ?? '',
                'title_style' => $this->resolveTextStyle($request, 'title', [
                    'size' => '48px',
                    'color' => '#ffffff',
                    'font' => 'inherit',
                ]),
                'description_style' => $this->resolveTextStyle($request, 'description', [
                    'size' => '19px',
                    'color' => '#ffffff',
                    'font' => 'inherit',
                ]),
                'button_style' => $this->resolveButtonStyle($request, [
                    'size' => '16px',
                    'color' => '#ffffff',
                    'background_color' => '#2563eb',
                    'font' => 'inherit',
                ]),
                'is_active' => true,
                'video_url' => '',
                'asset_source' => $this->resolveAssetSource($request),
                'media_id' => $request->filled('media_id') ? (int) $request->input('media_id') : null,
            ];

            if ($request->type === 'image') {
                $value['url'] = $this->handleImageUpload($request, $etablissement);
            } else {
                $value['url'] = $this->handleVideoUpload($request, $etablissement);
                $value['video_url'] = (string) ($request->input('external_url') ?: $request->input('video_url') ?: '');
            }

            $setting = Setting::create([
                'etablissement_id' => $etablissement->id,
                'group' => 'slider',
                'key' => 'slider_item_' . Str::random(8) . '_' . time(),
                'value' => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'order' => $order,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slide ajouté avec succès',
                'data' => $this->mapSettingSliderItem($setting),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Slider store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a slider item.
     */
    public function update(Request $request, $etablissementId, $id): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $source = $request->input('slide_source_kind', $request->input('source', 'setting'));

            if ($source === 'media') {
                return $this->updateMediaSlider($request, $etablissement, (int) $id);
            }

            $setting = Setting::where('etablissement_id', $etablissement->id)
                ->where('group', 'slider')
                ->where('id', $id)
                ->firstOrFail();

            $currentValue = $this->parseSettingValue($setting->value);

            $request->validate([
                'type' => 'sometimes|in:image,video',
                'media_id' => 'nullable|exists:cms.cms_media,id',
                'image_file' => 'nullable|file|image|max:5120',
                'video_file' => 'nullable|file|mimes:mp4,mov,ogg,webm|max:51200',
                'external_url' => 'nullable|url|max:2048',
                'video_url' => 'nullable|url|max:2048',
                'title' => 'nullable|string|max:255',
                'subtitle' => 'nullable|string|max:500',
                'button_text' => 'nullable|string|max:100',
                'button_link' => 'nullable|string|max:500',
                'title_size' => 'nullable|string|max:20',
                'title_color' => 'nullable|string|max:20',
                'title_font' => 'nullable|string|max:120',
                'description_size' => 'nullable|string|max:20',
                'description_color' => 'nullable|string|max:20',
                'description_font' => 'nullable|string|max:120',
                'button_size' => 'nullable|string|max:20',
                'button_color' => 'nullable|string|max:20',
                'button_bg_color' => 'nullable|string|max:20',
                'button_font' => 'nullable|string|max:120',
                'is_active' => 'boolean',
            ]);

            $value = [
                'type' => $request->type ?? ($currentValue['type'] ?? 'image'),
                'title' => $request->title ?? ($currentValue['title'] ?? ''),
                'subtitle' => $request->subtitle ?? ($currentValue['subtitle'] ?? ''),
                'button_text' => $request->button_text ?? ($currentValue['button_text'] ?? ''),
                'button_link' => $request->button_link ?? ($currentValue['button_link'] ?? ''),
                'title_style' => $this->resolveTextStyle($request, 'title', $currentValue['title_style'] ?? [
                    'size' => '48px',
                    'color' => '#ffffff',
                    'font' => 'inherit',
                ]),
                'description_style' => $this->resolveTextStyle($request, 'description', $currentValue['description_style'] ?? [
                    'size' => '19px',
                    'color' => '#ffffff',
                    'font' => 'inherit',
                ]),
                'button_style' => $this->resolveButtonStyle($request, $currentValue['button_style'] ?? [
                    'size' => '16px',
                    'color' => '#ffffff',
                    'background_color' => '#2563eb',
                    'font' => 'inherit',
                ]),
                'video_url' => $currentValue['video_url'] ?? '',
                'asset_source' => $currentValue['asset_source'] ?? 'upload',
                'media_id' => $currentValue['media_id'] ?? null,
                'is_active' => $request->has('is_active')
                    ? (bool) $request->boolean('is_active')
                    : ($currentValue['is_active'] ?? true),
            ];

            $hasNewFile = $request->hasFile('image_file')
                || $request->hasFile('video_file')
                || $request->filled('media_id')
                || $request->filled('external_url')
                || $request->filled('video_url');

            if ($hasNewFile) {
                if (
                    !empty($currentValue['url'])
                    && ($currentValue['asset_source'] ?? 'upload') === 'upload'
                    && empty($currentValue['media_id'])
                ) {
                    $this->deleteMediaFile($currentValue['url'], $etablissement);
                }

                $value['asset_source'] = $this->resolveAssetSource($request);
                $value['media_id'] = $request->filled('media_id') ? (int) $request->input('media_id') : null;

                if ($value['type'] === 'image') {
                    $value['url'] = $this->handleImageUpload($request, $etablissement);
                    $value['video_url'] = '';
                } else {
                    $value['url'] = $this->handleVideoUpload($request, $etablissement);
                    $value['video_url'] = (string) ($request->input('external_url') ?: $request->input('video_url') ?: '');
                }
            } else {
                $value['url'] = $currentValue['url'] ?? '';
                if (isset($currentValue['video_path'])) {
                    $value['video_path'] = $currentValue['video_path'];
                }
                if (isset($currentValue['video_html'])) {
                    $value['video_html'] = $currentValue['video_html'];
                }
            }

            $setting->update([
                'value' => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slide mis à jour avec succès',
                'data' => $this->mapSettingSliderItem($setting->fresh()),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Slider update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a slider item.
     */
    public function destroy(Request $request, $etablissementId, $id): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $source = $request->input('source', 'setting');

            if ($source === 'media') {
                $media = Media::where('etablissement_id', $etablissement->id)
                    ->where('id', $id)
                    ->firstOrFail();

                $media->update([
                    'is_slider' => false,
                    'order' => 0,
                    'button_text' => null,
                    'button_url' => null,
                ]);

                $this->mediaToSliderSyncService->remove($media->fresh());

                return response()->json([
                    'success' => true,
                    'message' => 'Média retiré du slider avec succès',
                ]);
            }

            $setting = Setting::where('etablissement_id', $etablissement->id)
                ->where('group', 'slider')
                ->where('id', $id)
                ->firstOrFail();

            $value = $this->parseSettingValue($setting->value);
            if (
                !empty($value['url'])
                && ($value['asset_source'] ?? 'upload') === 'upload'
                && empty($value['media_id'])
                && !$this->isExternalUrl($value['url'])
            ) {
                $this->deleteMediaFile($value['url'], $etablissement);
            }

            $setting->delete();
            $this->reorderSliders($etablissement->id);

            return response()->json([
                'success' => true,
                'message' => 'Slide supprimé avec succès',
            ]);
        } catch (\Throwable $e) {
            Log::error('Slider delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder slider items.
     */
    public function reorder(Request $request, $etablissementId): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);

            $request->validate([
                'orders' => 'required|array',
                'orders.*.item_id' => 'required|integer',
                'orders.*.source' => 'required|in:setting,media',
                'orders.*.order' => 'required|integer',
            ]);

            foreach ($request->orders as $item) {
                if (($item['source'] ?? 'setting') === 'media') {
                    Media::where('etablissement_id', $etablissement->id)
                        ->where('id', $item['item_id'])
                        ->update(['order' => $item['order']]);

                    $media = Media::where('etablissement_id', $etablissement->id)
                        ->where('id', $item['item_id'])
                        ->first();

                    if ($media && $media->is_slider) {
                        $this->mediaToSliderSyncService->sync($media);
                    }
                } else {
                    Setting::where('etablissement_id', $etablissement->id)
                        ->where('group', 'slider')
                        ->where('id', $item['item_id'])
                        ->update(['order' => $item['order']]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Ordre mis a jour',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Slider reorder error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle slider item active status.
     */
    public function toggleActive(Request $request, $etablissementId, $id): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $source = $request->input('source', 'setting');

            if ($source === 'media') {
                $media = Media::where('etablissement_id', $etablissement->id)
                    ->where('id', $id)
                    ->firstOrFail();

                $nextState = !$media->is_slider;
                $media->update([
                    'is_slider' => $nextState,
                    'order' => $nextState ? ($media->order ?: $this->getNextSliderOrder($etablissement->id)) : 0,
                    'button_text' => $nextState ? $media->button_text : null,
                    'button_url' => $nextState ? $media->button_url : null,
                ]);

                if ($nextState) {
                    $this->mediaToSliderSyncService->sync($media->fresh());
                } else {
                    $this->mediaToSliderSyncService->remove($media->fresh());
                }

                return response()->json([
                    'success' => true,
                    'message' => $nextState ? 'Média ajouté au slider' : 'Média retiré du slider',
                    'is_active' => $nextState,
                ]);
            }

            $setting = Setting::where('etablissement_id', $etablissement->id)
                ->where('group', 'slider')
                ->where('id', $id)
                ->firstOrFail();

            $value = $this->parseSettingValue($setting->value);
            $value['is_active'] = !($value['is_active'] ?? true);

            $setting->update([
                'value' => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]);

            return response()->json([
                'success' => true,
                'message' => $value['is_active'] ? 'Slide activé' : 'Slide désactivé',
                'is_active' => $value['is_active'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Slider toggle error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================
    // PRIVATE METHODS
    // ============================================

    private function mapSettingSliderItem(Setting $setting): array
    {
        $value = $this->parseSettingValue($setting->value);

        $url = $value['url'] ?? '';
        if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL) && !str_starts_with($url, '/storage/')) {
            if (Storage::disk('public')->exists($url)) {
                $url = Storage::disk('public')->url($url);
            }
        }

        return [
            'id' => $setting->id,
            'source_id' => $setting->id,
            'source' => 'setting',
            'type' => $value['type'] ?? 'image',
            'url' => $this->toAbsolutePublicUrl($url),
            'video_url' => $value['video_url'] ?? '',
            'video_path' => $value['video_path'] ?? '',
            'video_html' => $value['video_html'] ?? null,
            'asset_source' => $value['asset_source'] ?? 'upload',
            'media_id' => $value['media_id'] ?? null,
            'title' => $value['title'] ?? '',
            'subtitle' => $value['subtitle'] ?? '',
            'button_text' => $value['button_text'] ?? '',
            'button_link' => $value['button_link'] ?? '',
            'title_style' => $value['title_style'] ?? [],
            'description_style' => $value['description_style'] ?? [],
            'button_style' => $value['button_style'] ?? [],
            'is_active' => $value['is_active'] ?? true,
            'order' => $setting->order ?? 0,
        ];
    }

    private function mapMediaSliderItem(Media $media): array
    {
        $videoUrl = (string) ($media->video_url ?? '');
        $url = $videoUrl !== '' ? $videoUrl : $media->url;
        $isVideo = $media->type === 'video' || str_starts_with((string) $media->mime_type, 'video/');
        $linkedSlider = $this->mediaToSliderSyncService->findLinkedSlider((int) $media->id);
        $settings = (array) ($linkedSlider?->settings ?? []);

        return [
            'id' => $media->id,
            'source_id' => $media->id,
            'source' => 'media',
            'type' => $isVideo ? 'video' : 'image',
            'url' => $this->toAbsolutePublicUrl($url),
            'video_url' => $videoUrl,
            'video_path' => $media->path,
            'video_html' => null,
            'title' => $media->title ?: ($media->name ?? ''),
            'subtitle' => $media->description ?? '',
            'button_text' => $media->button_text ?? '',
            'button_link' => $media->button_url ?? '',
            'title_style' => $settings['title_style'] ?? [],
            'description_style' => $settings['description_style'] ?? [],
            'button_style' => $settings['button_style'] ?? [],
            'is_active' => (bool) $media->is_slider,
            'order' => $media->order ?? 0,
        ];
    }

    private function getNextSliderOrder(int $etablissementId): int
    {
        $settingOrder = (int) (
            Setting::where('etablissement_id', $etablissementId)
                ->where('group', 'slider')
                ->max('order') ?? 0
        );

        $mediaOrder = (int) (
            Media::where('etablissement_id', $etablissementId)
                ->where('is_slider', true)
                ->max('order') ?? 0
        );

        return max($settingOrder, $mediaOrder) + 1;
    }

    private function updateMediaSlider(Request $request, Etablissement $etablissement, int $mediaId): JsonResponse
    {
        $media = Media::where('etablissement_id', $etablissement->id)
            ->where('id', $mediaId)
            ->firstOrFail();

        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:500',
            'title_size' => 'nullable|string|max:20',
            'title_color' => 'nullable|string|max:20',
            'title_font' => 'nullable|string|max:120',
            'description_size' => 'nullable|string|max:20',
            'description_color' => 'nullable|string|max:20',
            'description_font' => 'nullable|string|max:120',
            'button_size' => 'nullable|string|max:20',
            'button_color' => 'nullable|string|max:20',
            'button_bg_color' => 'nullable|string|max:20',
            'button_font' => 'nullable|string|max:120',
        ]);

        $media->update([
            'title' => $request->input('title'),
            'description' => $request->input('subtitle'),
            'button_text' => $request->input('button_text'),
            'button_url' => $request->input('button_link'),
            'is_slider' => true,
            'order' => $media->order ?: $this->getNextSliderOrder($etablissement->id),
        ]);

        $linkedSlider = $this->mediaToSliderSyncService->sync($media->fresh());

        if ($linkedSlider) {
            $settings = (array) ($linkedSlider->settings ?? []);
            $linkedSlider->update([
                'settings' => array_merge($settings, [
                    'title_style' => $this->resolveTextStyle($request, 'title', $settings['title_style'] ?? [
                        'size' => '48px',
                        'color' => '#ffffff',
                        'font' => 'inherit',
                    ]),
                    'description_style' => $this->resolveTextStyle($request, 'description', $settings['description_style'] ?? [
                        'size' => '19px',
                        'color' => '#ffffff',
                        'font' => 'inherit',
                    ]),
                    'button_style' => $this->resolveButtonStyle($request, $settings['button_style'] ?? [
                        'size' => '16px',
                        'color' => '#ffffff',
                        'background_color' => '#2563eb',
                        'font' => 'inherit',
                    ]),
                ]),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Slide média mis à jour avec succès',
            'data' => $this->mapMediaSliderItem($media->fresh()),
        ]);
    }

    /**
     * Handle image upload from media library or direct upload.
     */
    private function handleImageUpload(Request $request, $etablissement): string
    {
        if ($request->filled('external_url')) {
            return (string) $request->input('external_url');
        }

        if ($request->media_id) {
            $media = Media::find($request->media_id);
            if ($media && $media->isImage()) {
                return $media->url;
            }
        }

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $path = "sliders/{$etablissement->id}/images";
            $filename = $this->resolveOriginalFilename($file, $path);

            $storedPath = $file->storeAs($path, $filename, 'public');

            return $this->toAbsolutePublicUrl(Storage::disk('public')->url($storedPath));
        }

        return '';
    }

    /**
     * Handle video upload.
     */
    private function handleVideoUpload(Request $request, $etablissement): string
    {
        if ($request->filled('external_url')) {
            return (string) $request->input('external_url');
        }

        if ($request->filled('video_url')) {
            return (string) $request->input('video_url');
        }

        if ($request->media_id) {
            $media = Media::find($request->media_id);
            if ($media && ($media->type === 'video' || str_starts_with((string) $media->mime_type, 'video/'))) {
                return $media->video_url ?: $media->url;
            }
        }

        if ($request->hasFile('video_file')) {
            $file = $request->file('video_file');
            $path = "sliders/{$etablissement->id}/videos";
            $filename = $this->resolveOriginalFilename($file, $path);

            $storedPath = $file->storeAs($path, $filename, 'public');

            return $this->toAbsolutePublicUrl(Storage::disk('public')->url($storedPath));
        }

        return '';
    }

    /**
     * Delete media file from storage.
     */
    private function deleteMediaFile($url, $etablissement): void
    {
        if (empty($url)) {
            return;
        }

        if ($this->isExternalUrl($url)) {
            return;
        }

        try {
            if (strpos($url, '/storage/') !== false) {
                $relativePath = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                    Log::info('Deleted local file: ' . $relativePath);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to delete media file: ' . $e->getMessage());
        }
    }

    /**
     * Check if URL is external (YouTube, Vimeo, etc.).
     */
    private function isExternalUrl($url): bool
    {
        if (empty($url)) {
            return false;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = (string) parse_url($url, PHP_URL_HOST);
        $localHosts = array_filter([
            (string) parse_url((string) config('app.url'), PHP_URL_HOST),
            (string) parse_url((string) env('CDN_URL', ''), PHP_URL_HOST),
            (string) parse_url((string) env('THEME_CDN_URL', ''), PHP_URL_HOST),
        ]);

        if ($host !== '' && !in_array($host, $localHosts, true)) {
            return true;
        }

        $externalDomains = [
            'youtube.com',
            'youtu.be',
            'vimeo.com',
            'dailymotion.com',
            'facebook.com',
            'twitter.com',
            'instagram.com',
            'tiktok.com',
        ];

        foreach ($externalDomains as $domain) {
            if (strpos($url, $domain) !== false) {
                return true;
            }
        }

        return false;
    }

    private function resolveOriginalFilename($file, string $directory): string
    {
        $originalName = trim((string) $file->getClientOriginalName());
        $originalName = basename(str_replace(['\\', '/'], '-', $originalName));
        $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?: '';

        if ($originalName === '' || $originalName === '.' || $originalName === '..') {
            $extension = $file->getClientOriginalExtension();
            $originalName = 'media-' . Str::uuid() . ($extension ? '.' . $extension : '');
        }

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $nameOnly = pathinfo($originalName, PATHINFO_FILENAME);
        $candidate = $originalName;
        $counter = 2;

        while (Storage::disk('public')->exists(trim($directory, '/') . '/' . $candidate)) {
            $candidate = $nameOnly . '-' . $counter . ($extension ? '.' . $extension : '');
            $counter++;
        }

        return $candidate;
    }

    private function toAbsolutePublicUrl(?string $url): string
    {
        if (empty($url)) {
            return '';
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');

        return $baseUrl !== '' ? $baseUrl . '/' . ltrim($url, '/') : url($url);
    }

    /**
     * Reorder settings-based sliders after deletion.
     */
    private function reorderSliders($etablissementId): void
    {
        $sliders = Setting::where('etablissement_id', $etablissementId)
            ->where('group', 'slider')
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $order = 1;
        foreach ($sliders as $slider) {
            $slider->update(['order' => $order]);
            $order++;
        }
    }

    /**
     * Parse setting value (handle both JSON and string).
     */
    private function parseSettingValue($value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $cleanValue = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value);
            $decoded = json_decode($cleanValue, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            Log::warning('Failed to parse JSON value', ['value' => substr($value, 0, 200)]);

            return [];
        }

        return is_array($value) ? $value : [];
    }

    private function resolveAssetSource(Request $request): string
    {
        $assetSource = (string) $request->input('asset_source', '');

        if (in_array($assetSource, ['upload', 'url', 'media'], true)) {
            return $assetSource;
        }

        if ($request->filled('media_id')) {
            return 'media';
        }

        if ($request->filled('external_url') || $request->filled('video_url')) {
            return 'url';
        }

        return 'upload';
    }

    private function resolveTextStyle(Request $request, string $prefix, array $fallback): array
    {
        return [
            'size' => $this->cleanCssSize((string) $request->input($prefix . '_size', $fallback['size'] ?? '')),
            'color' => $this->cleanCssColor((string) $request->input($prefix . '_color', $fallback['color'] ?? '')),
            'font' => $this->cleanFontFamily((string) $request->input($prefix . '_font', $fallback['font'] ?? 'inherit')),
        ];
    }

    private function resolveButtonStyle(Request $request, array $fallback): array
    {
        return [
            'size' => $this->cleanCssSize((string) $request->input('button_size', $fallback['size'] ?? '')),
            'color' => $this->cleanCssColor((string) $request->input('button_color', $fallback['color'] ?? '')),
            'background_color' => $this->cleanCssColor((string) $request->input('button_bg_color', $fallback['background_color'] ?? '#2563eb')),
            'font' => $this->cleanFontFamily((string) $request->input('button_font', $fallback['font'] ?? 'inherit')),
        ];
    }

    private function cleanCssSize(string $value): string
    {
        $value = trim($value);

        return preg_match('/^\d{1,3}(\.\d{1,2})?(px|rem|em|%)$/', $value) ? $value : '';
    }

    private function cleanCssColor(string $value): string
    {
        $value = trim($value);

        return preg_match('/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $value) ? $value : '';
    }

    private function cleanFontFamily(string $value): string
    {
        $value = trim($value);

        return preg_match('/^[A-Za-z0-9\s,\-"\\\']{1,120}$/', $value) ? $value : 'inherit';
    }
}
