<?php

namespace Vendor\Cms\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Continent;
use App\Models\Country;
use App\Models\Etablissement;
use App\Models\Province;
use App\Models\Region;
use App\Models\Secteur;
use App\Models\Ville;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Vendor\Cms\Models\Media;
use Vendor\Cms\Services\CdnMediaService;
use Vendor\Cms\Services\MediaToSliderSyncService;

class MediaController extends Controller
{
    public function __construct(
        private CdnMediaService $cdnMediaService,
        private MediaToSliderSyncService $mediaToSliderSyncService
    )
    {
    }

    public function index(Request $request, $etablissementId)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);

        if (!$this->userHasAccess($request->user(), $etablissement)) {
            abort(403, 'Accès non autorisé');
        }

        $query = Media::with(['continent', 'country', 'province', 'region', 'ville', 'secteur'])
            ->where('etablissement_id', $etablissement->id);

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('folder')) {
            $query->where('folder', $request->folder);
        }

        foreach (['continent_id', 'country_id', 'province_id', 'region_id', 'ville_id', 'secteur_id'] as $geoField) {
            if ($request->filled($geoField)) {
                $query->where($geoField, $request->input($geoField));
            }
        }

        if ($request->has('is_slider') && $request->input('is_slider') !== '') {
            $query->where('is_slider', (bool) $request->boolean('is_slider'));
        }

        foreach ($this->galleryFlagFields() as $galleryField) {
            if ($request->has($galleryField) && $request->input($galleryField) !== '') {
                $query->where($galleryField, (bool) $request->boolean($galleryField));
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('original_name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $media = $query->orderBy('order', 'asc')->orderByDesc('created_at')->paginate(24);

        $stats = [
            'total' => Media::where('etablissement_id', $etablissement->id)->count(),
            'images' => Media::where('etablissement_id', $etablissement->id)->where('type', 'image')->count(),
            'videos' => Media::where('etablissement_id', $etablissement->id)->where('type', 'video')->count(),
            'documents' => Media::where('etablissement_id', $etablissement->id)->where('type', 'document')->count(),
            'size' => $this->getTotalSize($etablissement->id),
            'folders' => $this->getFolders($etablissement->id),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $media,
                'stats' => $stats,
                'locations' => [
                    'continents' => Continent::select('id', 'name')->orderBy('name')->get(),
                    'countries' => Country::select('id', 'name', 'continent_id')->orderBy('name')->get(),
                    'provinces' => Province::select('id', 'name', 'country_id')->orderBy('name')->get(),
                    'regions' => Region::select('id', 'name', 'province_id')->orderBy('name')->get(),
                    'villes' => Ville::select('id', 'name', 'region_id', 'province_id', 'country_id')->orderBy('name')->get(),
                    'secteurs' => Secteur::select('id', 'name', 'region_id')->orderBy('name')->get(),
                ],
            ]);
        }

        return view('cms::admin.media.index', compact('media', 'stats', 'etablissement'));
    }

    public function upload(Request $request, $etablissementId): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'nullable|file|max:102400',
                'name' => 'nullable|string|max:255',
                'folder' => 'nullable|string',
                'type' => 'nullable|in:image,video',
                'video_url' => 'nullable|url|max:2048',
                'continent_id' => 'nullable|integer',
                'country_id' => 'nullable|integer',
                'province_id' => 'nullable|integer',
                'region_id' => 'nullable|integer',
                'ville_id' => 'nullable|integer',
                'secteur_id' => 'nullable|integer',
                'is_slider' => 'nullable|boolean',
                'is_main_gallery' => 'nullable|boolean',
                'is_facebook_gallery' => 'nullable|boolean',
                'is_instagram_gallery' => 'nullable|boolean',
                'is_pinterest_gallery' => 'nullable|boolean',
                'order' => 'nullable|integer|min:0',
                'button_text' => 'nullable|string|max:120',
                'button_url' => 'nullable|url|max:2048',
            ]);

            $etablissement = Etablissement::findOrFail($etablissementId);
            if (!$this->userHasAccess($request->user(), $etablissement)) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }

            $file = $request->file('file');
            $videoUrl = (string) $request->input('video_url', '');
            $requestedType = $request->input('type');
            if (!$file && empty($videoUrl)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez fournir un fichier ou une URL vidéo.',
                ], 422);
            }

            $isExternalVideo = !$file && !empty($videoUrl) && $requestedType === 'video';
            if (!$file && !$isExternalVideo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Type de média invalide sans fichier.',
                ], 422);
            }

            $originalName = $isExternalVideo ? basename(parse_url($videoUrl, PHP_URL_PATH) ?: 'video-url') : $file->getClientOriginalName();
            $extension = $isExternalVideo ? 'url' : $file->getClientOriginalExtension();
            $mimeType = $isExternalVideo ? 'video/url' : $file->getMimeType();
            $size = $isExternalVideo ? 0 : $file->getSize();
            $type = $requestedType ?: $this->getFileType((string) $mimeType);
            $folder = trim((string) $request->input('folder', '/'), '/');
            $filename = Str::uuid() . ($isExternalVideo ? '' : ('.' . $extension));
            $isSlider = $request->boolean('is_slider');
            $buttonText = $isSlider ? $request->input('button_text') : null;
            $buttonUrl = $isSlider ? $request->input('button_url') : null;
            $galleryAssignments = $this->galleryAssignments($request, $type);

            $uploadResult = ['url' => null, 'metadata' => []];
            if (!$isExternalVideo) {
                $uploadResult = $this->cdnMediaService->upload($file, "cms/media/{$etablissement->id}/{$folder}");
                if (!($uploadResult['success'] ?? false)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de l\'enregistrement local: ' . ($uploadResult['error'] ?? 'inconnue'),
                    ], 422);
                }

                if (!empty($uploadResult['path'])) {
                    $filename = basename((string) $uploadResult['path']);
                }
            }

            $width = null;
            $height = null;
            if (!$isExternalVideo && str_starts_with((string) $mimeType, 'image/')) {
                $imageInfo = @getimagesize($file->getPathname());
                if ($imageInfo) {
                    $width = $imageInfo[0] ?? null;
                    $height = $imageInfo[1] ?? null;
                }
            }

            $media = Media::create([
                'etablissement_id' => $etablissement->id,
                'user_id' => optional($request->user())->id,
                'name' => $request->input('name', pathinfo($originalName, PATHINFO_FILENAME)),
                'original_name' => $originalName,
                'filename' => $filename,
                'path' => $isExternalVideo ? $videoUrl : $uploadResult['url'],
                'size' => $size,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'type' => $type,
                'video_url' => $videoUrl ?: null,
                'folder' => $folder ?: '/',
                'width' => $width,
                'height' => $height,
                'is_public' => true,
                'metadata' => ['storage' => $uploadResult['metadata'] ?? []],
                'continent_id' => $request->input('continent_id'),
                'country_id' => $request->input('country_id'),
                'province_id' => $request->input('province_id'),
                'region_id' => $request->input('region_id'),
                'ville_id' => $request->input('ville_id'),
                'secteur_id' => $request->input('secteur_id'),
                'is_slider' => $isSlider,
                'is_main_gallery' => $galleryAssignments['is_main_gallery'],
                'is_facebook_gallery' => $galleryAssignments['is_facebook_gallery'],
                'is_instagram_gallery' => $galleryAssignments['is_instagram_gallery'],
                'is_pinterest_gallery' => $galleryAssignments['is_pinterest_gallery'],
                'order' => (int) $request->input('order', 0),
                'button_text' => $buttonText,
                'button_url' => $buttonUrl,
            ]);

            $this->syncLinkedSlider($media->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Média enregistré avec succès',
                'media' => $media,
                'url' => $media->url,
            ]);
        } catch (\Throwable $e) {
            Log::error('Media upload error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur upload: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $etablissementId, $id): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            if (!$this->userHasAccess($request->user(), $etablissement)) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }

            $media = Media::where('id', $id)->where('etablissement_id', $etablissement->id)->firstOrFail();
            $this->deleteMediaAsset($media);
            $this->mediaToSliderSyncService->remove($media);
            $media->delete();

            return response()->json(['success' => true, 'message' => 'Fichier supprimé avec succès']);
        } catch (\Throwable $e) {
            Log::error('Media delete error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur suppression: ' . $e->getMessage()], 500);
        }
    }

    public function bulkDelete(Request $request, $etablissementId): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            if (!$this->userHasAccess($request->user(), $etablissement)) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }

            $ids = (array) $request->input('ids', []);
            if (empty($ids)) {
                return response()->json(['success' => false, 'message' => 'Aucun fichier sélectionné'], 400);
            }

            Media::where('etablissement_id', $etablissement->id)
                ->whereIn('id', $ids)
                ->get()
                ->each(function (Media $media): void {
                    $this->deleteMediaAsset($media);
                    $this->mediaToSliderSyncService->remove($media);
                });

            Media::where('etablissement_id', $etablissement->id)->whereIn('id', $ids)->delete();

            return response()->json(['success' => true, 'message' => count($ids) . ' fichier(s) supprimé(s) avec succès']);
        } catch (\Throwable $e) {
            Log::error('Bulk delete error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur suppression: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $etablissementId, $id): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'nullable|string|max:255',
                'alt' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'video_url' => 'nullable|url|max:2048',
                'type' => 'nullable|in:image,video',
                'folder' => 'nullable|string',
                'continent_id' => 'nullable|integer',
                'country_id' => 'nullable|integer',
                'province_id' => 'nullable|integer',
                'region_id' => 'nullable|integer',
                'ville_id' => 'nullable|integer',
                'secteur_id' => 'nullable|integer',
                'is_slider' => 'nullable|boolean',
                'is_main_gallery' => 'nullable|boolean',
                'is_facebook_gallery' => 'nullable|boolean',
                'is_instagram_gallery' => 'nullable|boolean',
                'is_pinterest_gallery' => 'nullable|boolean',
                'order' => 'nullable|integer|min:0',
                'button_text' => 'nullable|string|max:120',
                'button_url' => 'nullable|url|max:2048',
            ]);

            $etablissement = Etablissement::findOrFail($etablissementId);
            if (!$this->userHasAccess($request->user(), $etablissement)) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }

            $media = Media::where('id', $id)->where('etablissement_id', $etablissement->id)->firstOrFail();
            $isSlider = $request->boolean('is_slider');
            $updatedVideoUrl = (string) $request->input('video_url', '');
            $updatedType = $request->input('type', $media->type);
            $galleryAssignments = $this->galleryAssignments($request, $updatedType);
            $media->update([
                'name' => $request->input('name', $media->name),
                'alt' => $request->input('alt'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'video_url' => $updatedVideoUrl ?: null,
                'path' => $updatedVideoUrl !== '' && $updatedType === 'video'
                    ? $updatedVideoUrl
                    : $media->path,
                'type' => $updatedType,
                'folder' => $request->input('folder', $media->folder),
                'continent_id' => $request->input('continent_id'),
                'country_id' => $request->input('country_id'),
                'province_id' => $request->input('province_id'),
                'region_id' => $request->input('region_id'),
                'ville_id' => $request->input('ville_id'),
                'secteur_id' => $request->input('secteur_id'),
                'is_slider' => $isSlider,
                'is_main_gallery' => $galleryAssignments['is_main_gallery'],
                'is_facebook_gallery' => $galleryAssignments['is_facebook_gallery'],
                'is_instagram_gallery' => $galleryAssignments['is_instagram_gallery'],
                'is_pinterest_gallery' => $galleryAssignments['is_pinterest_gallery'],
                'order' => (int) $request->input('order', 0),
                'button_text' => $isSlider ? $request->input('button_text') : null,
                'button_url' => $isSlider ? $request->input('button_url') : null,
            ]);

            $this->syncLinkedSlider($media->fresh());

            return response()->json(['success' => true, 'message' => 'Média mis à jour avec succès', 'media' => $media]);
        } catch (\Throwable $e) {
            Log::error('Media update error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur mise à jour: ' . $e->getMessage()], 500);
        }
    }

    public function folder(Request $request, $etablissementId, $folder)
    {
        $request->merge(['folder' => $folder]);
        return $this->index($request, $etablissementId);
    }

    public function createFolder(Request $request, $etablissementId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Le dossier sera créé automatiquement lors du premier upload',
            'folder' => trim((string) $request->input('name', ''), '/'),
        ]);
    }

    public function export(Request $request, $etablissementId)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);
        if (!$this->userHasAccess($request->user(), $etablissement)) {
            abort(403, 'Accès non autorisé');
        }

        $media = Media::where('etablissement_id', $etablissement->id)->orderByDesc('created_at')->get();
        $filename = 'media_' . ($etablissement->slug ?? $etablissement->id) . '_' . date('Y-m-d_H-i-s') . '.csv';

        return response()->stream(function () use ($media) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Type', 'URL', 'Slider', 'Ordre', 'Créé le']);
            foreach ($media as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->name,
                    $item->type,
                    $item->path,
                    $item->is_slider ? '1' : '0',
                    $item->order,
                    optional($item->created_at)->format('d/m/Y H:i'),
                ]);
            }
            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function getMedia(Request $request, $etablissementId, $id): JsonResponse
    {
        $etablissement = Etablissement::findOrFail($etablissementId);
        if (!$this->userHasAccess($request->user(), $etablissement)) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $media = Media::with(['continent', 'country', 'province', 'region', 'ville', 'secteur'])
            ->where('id', $id)
            ->where('etablissement_id', $etablissement->id)
            ->firstOrFail();

        return response()->json(['success' => true, 'media' => $media, 'url' => $media->url]);
    }

    public function locations(Request $request, $etablissementId, string $level): JsonResponse
    {
        $etablissement = Etablissement::findOrFail($etablissementId);
        if (!$this->userHasAccess($request->user(), $etablissement)) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $level = strtolower(trim($level));
        $items = collect();

        switch ($level) {
            case 'continents':
                $items = Continent::select('id', 'name')->orderBy('name')->get();
                break;
            case 'countries':
                $items = Country::select('id', 'name')
                    ->when($request->filled('continent_id'), fn ($q) => $q->where('continent_id', $request->integer('continent_id')))
                    ->orderBy('name')
                    ->get();
                break;
            case 'provinces':
                $items = Province::select('id', 'name')
                    ->when($request->filled('country_id'), fn ($q) => $q->where('country_id', $request->integer('country_id')))
                    ->orderBy('name')
                    ->get();
                break;
            case 'regions':
                $items = Region::select('id', 'name')
                    ->when($request->filled('province_id'), fn ($q) => $q->where('province_id', $request->integer('province_id')))
                    ->orderBy('name')
                    ->get();
                break;
            case 'villes':
                $items = Ville::select('id', 'name')
                    ->when($request->filled('region_id'), fn ($q) => $q->where('region_id', $request->integer('region_id')))
                    ->orderBy('name')
                    ->get();
                break;
            case 'secteurs':
                $items = Secteur::select('id', 'name')
                    ->when($request->filled('region_id'), fn ($q) => $q->where('region_id', $request->integer('region_id')))
                    ->orderBy('name')
                    ->get();
                break;
            default:
            return response()->json(['success' => false, 'message' => 'Niveau de localisation invalide'], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $items,
            'level' => $level,
        ]);
    }

    protected function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        return 'document';
    }

    protected function getTotalSize($etablissementId): string
    {
        $total = Media::where('etablissement_id', $etablissementId)->sum('size');
        $bytes = max((float) $total, 0);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = $bytes > 0 ? (int) floor(log($bytes) / log(1024)) : 0;
        $pow = min($pow, count($units) - 1);
        $bytes = $bytes / pow(1024, $pow ?: 1);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    protected function getFolders($etablissementId): array
    {
        return Media::where('etablissement_id', $etablissementId)
            ->select('folder')
            ->distinct()
            ->pluck('folder')
            ->filter()
            ->values()
            ->all();
    }

    protected function userHasAccess($user, $etablissement): bool
    {
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if ($user && method_exists($user, 'etablissement') && $user->etablissement && $user->etablissement->id === $etablissement->id) {
            return true;
        }

        if ($user && method_exists($user, 'etablissements') && $user->etablissements->contains($etablissement)) {
            return true;
        }

        return false;
    }

    protected function syncLinkedSlider(Media $media): void
    {
        if ($media->is_slider) {
            $this->mediaToSliderSyncService->sync($media);

            return;
        }

        $this->mediaToSliderSyncService->remove($media);
    }

    protected function galleryAssignments(Request $request, string $type): array
    {
        $isGalleryMedia = in_array($type, ['image', 'video'], true)
            || str_starts_with($type, 'image/')
            || str_starts_with($type, 'video/');

        if (!$isGalleryMedia) {
            return array_fill_keys($this->galleryFlagFields(), false);
        }

        $assignments = [];
        foreach ($this->galleryFlagFields() as $field) {
            $assignments[$field] = $request->boolean($field);
        }

        return $assignments;
    }

    protected function galleryFlagFields(): array
    {
        return [
            'is_main_gallery',
            'is_facebook_gallery',
            'is_instagram_gallery',
            'is_pinterest_gallery',
        ];
    }

    protected function deleteMediaAsset(Media $media): void
    {
        if (!empty($media->video_url) && $media->path === $media->video_url) {
            return;
        }

        $this->cdnMediaService->delete($media->path);
    }
}

