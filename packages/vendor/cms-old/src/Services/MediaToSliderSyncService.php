<?php

namespace Vendor\Cms\Services;

use App\Models\Slider;
use Illuminate\Support\Facades\Log;
use Vendor\Cms\Models\Media;

class MediaToSliderSyncService
{
    public function sync(Media $media): ?Slider
    {
        if (!$media->is_slider) {
            $this->remove($media);

            return null;
        }

        $slider = $this->findLinkedSlider((int) $media->id) ?? new Slider();

        if (method_exists($slider, 'trashed') && $slider->trashed()) {
            $slider->restore();
        }

        $slider->fill($this->buildPayload($media, (array) ($slider->settings ?? [])));
        $slider->save();

        return $slider->fresh();
    }

    public function remove(Media|int $media): void
    {
        $mediaId = $media instanceof Media ? (int) $media->id : (int) $media;
        $slider = $this->findLinkedSlider($mediaId);

        if (!$slider) {
            return;
        }

        if (method_exists($slider, 'trashed') && !$slider->trashed()) {
            $slider->delete();
        }
    }

    public function findLinkedSlider(int $mediaId): ?Slider
    {
        try {
            return Slider::withTrashed()
                ->where('settings->source_type', 'cms_media')
                ->where('settings->cms_media_id', $mediaId)
                ->first();
        } catch (\Throwable $e) {
            Log::warning('Unable to query slider by JSON settings, falling back to LIKE search', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);

            return Slider::withTrashed()
                ->where('settings', 'like', '%"source_type":"cms_media"%')
                ->where('settings', 'like', '%"cms_media_id":' . $mediaId . '%')
                ->first();
        }
    }

    private function buildPayload(Media $media, array $existingSettings = []): array
    {
        $type = $this->resolveSliderType($media);
        $name = trim((string) ($media->title ?: $media->name ?: $media->original_name ?: 'Media slider'));
        $description = (string) ($media->description ?? '');
        $videoUrl = (string) ($media->video_url ?? '');
        $fileUrl = (string) $media->url;

        $imagePath = null;
        $videoPath = null;
        $resolvedVideoUrl = null;
        $videoType = null;
        $thumbnailPath = null;

        if ($type === 'image') {
            $imagePath = $fileUrl;
            $thumbnailPath = $fileUrl;
        } else {
            if ($videoUrl !== '') {
                $resolvedVideoUrl = $videoUrl;
                $videoPath = $videoUrl;
                $videoType = $this->detectVideoPlatform($videoUrl);
            } else {
                $videoPath = $fileUrl;
                $videoType = 'upload';
            }
        }

        return [
            'name' => $name,
            'description' => $description,
            'type' => $type,
            'image_path' => $imagePath,
            'video_path' => $videoPath,
            'video_type' => $videoType,
            'video_url' => $resolvedVideoUrl,
            'thumbnail_path' => $thumbnailPath,
            'order' => max(0, (int) ($media->order ?? 0)),
            'is_active' => true,
            'button_text' => $media->button_text,
            'button_url' => $media->button_url,
            'country_id' => $media->country_id,
            'province_id' => $media->province_id,
            'region_id' => $media->region_id,
            'ville_id' => $media->ville_id,
            'settings' => array_merge($existingSettings, [
                'source_type' => 'cms_media',
                'cms_media_id' => (int) $media->id,
                'cms_etablissement_id' => (int) $media->etablissement_id,
            ]),
        ];
    }

    private function resolveSliderType(Media $media): string
    {
        if ($media->type === 'video') {
            return 'video';
        }

        if (!empty($media->video_url)) {
            return 'video';
        }

        if (str_starts_with((string) $media->mime_type, 'video/')) {
            return 'video';
        }

        return 'image';
    }

    private function detectVideoPlatform(string $url): string
    {
        $url = strtolower($url);

        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'youtube';
        }

        if (str_contains($url, 'vimeo.com')) {
            return 'vimeo';
        }

        return 'other';
    }
}
