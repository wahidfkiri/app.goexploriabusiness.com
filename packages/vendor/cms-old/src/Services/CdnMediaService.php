<?php

namespace Vendor\Cms\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CdnMediaService
{
    public function __construct()
    {
    }

    public function upload(UploadedFile $file, string $targetPath = 'cms/media'): array
    {
        $originalName = $file->getClientOriginalName();

        try {
            $filename = $this->resolveOriginalFilename($file, trim($targetPath, '/'));
            $relativePath = trim($targetPath, '/') . '/' . $filename;
            $storedPath = $file->storeAs(trim($targetPath, '/'), $filename, 'public');
            $publicUrl = $this->toAbsoluteUrl(Storage::disk('public')->url($storedPath));

            return [
                'success' => true,
                'url' => $publicUrl,
                'path' => $relativePath,
                'metadata' => [
                    'driver' => 'local',
                    'disk' => 'public',
                    'relative_path' => $relativePath,
                    'original_name' => $originalName,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function delete(?string $pathOrUrl): bool
    {
        $relativePath = $this->extractRelativePath($pathOrUrl);

        if ($relativePath === null) {
            return false;
        }

        return Storage::disk('public')->delete($relativePath);
    }

    private function resolveOriginalFilename(UploadedFile $file, string $directory): string
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

    private function extractRelativePath(?string $pathOrUrl): ?string
    {
        if (empty($pathOrUrl)) {
            return null;
        }

        if (!preg_match('/^https?:\/\//i', $pathOrUrl)) {
            return ltrim($pathOrUrl, '/');
        }

        $parsedPath = (string) parse_url($pathOrUrl, PHP_URL_PATH);
        $storagePrefix = '/storage/';
        $position = strpos($parsedPath, $storagePrefix);

        if ($position === false) {
            return null;
        }

        return ltrim(substr($parsedPath, $position + strlen($storagePrefix)), '/');
    }

    private function toAbsoluteUrl(string $path): string
    {
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');

        return $baseUrl !== '' ? $baseUrl . '/' . ltrim($path, '/') : url($path);
    }
}
