<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'cms';

    public function up(): void
    {
        try {
            DB::connection('cms')->statement('ALTER TABLE cms_media MODIFY path TEXT');
        } catch (\Throwable $e) {
            // Ignore on database engines that do not support this syntax.
        }

        DB::connection('cms')
            ->table('cms_media')
            ->whereNotNull('path')
            ->orderBy('id')
            ->chunkById(100, function ($media): void {
                foreach ($media as $item) {
                    $normalized = $this->toAbsolutePublicUrl($item->path);

                    if ($normalized !== $item->path) {
                        DB::connection('cms')
                            ->table('cms_media')
                            ->where('id', $item->id)
                            ->update(['path' => $normalized]);
                    }
                }
            });
    }

    public function down(): void
    {
        DB::connection('cms')
            ->table('cms_media')
            ->whereNotNull('path')
            ->orderBy('id')
            ->chunkById(100, function ($media): void {
                foreach ($media as $item) {
                    $relative = $this->toRelativePublicPath($item->path);

                    if ($relative !== null && $relative !== $item->path) {
                        DB::connection('cms')
                            ->table('cms_media')
                            ->where('id', $item->id)
                            ->update(['path' => $relative]);
                    }
                }
            });
    }

    private function toAbsolutePublicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }

        $path = trim($path);

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            $publicPath = '/' . ltrim($path, '/');
        } else {
            $publicPath = '/storage/' . ltrim($path, '/');
        }

        return $baseUrl !== '' ? $baseUrl . $publicPath : url($publicPath);
    }

    private function toRelativePublicPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }

        if (!preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $parsedPath = (string) parse_url($path, PHP_URL_PATH);
        $storagePrefix = '/storage/';
        $position = strpos($parsedPath, $storagePrefix);

        if ($position === false) {
            return null;
        }

        return ltrim(substr($parsedPath, $position + strlen($storagePrefix)), '/');
    }
};
