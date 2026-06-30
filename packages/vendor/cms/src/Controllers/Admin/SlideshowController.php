<?php

namespace Vendor\Cms\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Vendor\Cms\Models\CmsSlideshow;

class SlideshowController extends Controller
{
    public function index($etablissementId): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);

            $items = CmsSlideshow::where('etablissement_id', $etablissement->id)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get()
                ->map(fn (CmsSlideshow $slideshow) => $this->mapSlideshow($slideshow))
                ->values();

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Throwable $e) {
            Log::error('Slideshow index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du slideshow',
            ], 500);
        }
    }

    public function store(Request $request, $etablissementId): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $data = $this->validatedData($request, true);
            $media = $this->resolveVideoSource($request, $etablissement);

            $slideshow = CmsSlideshow::create([
                'etablissement_id' => $etablissement->id,
                'title' => $data['title'] ?? null,
                'subtitle' => $data['subtitle'] ?? null,
                'source' => $media['source'],
                'video_url' => $media['video_url'],
                'video_path' => $media['video_path'],
                'poster_url' => $data['poster_url'] ?? null,
                'button_text' => $data['button_text'] ?? null,
                'button_url' => $data['button_url'] ?? null,
                'button_target' => $data['button_target'] ?? '_self',
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $this->nextOrder($etablissement->id),
                'options' => [
                    'autoplay' => $request->boolean('autoplay', true),
                    'muted' => $request->boolean('muted', true),
                    'loop' => $request->boolean('loop', true),
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vidéo slideshow ajoutée avec succès',
                'data' => $this->mapSlideshow($slideshow),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Slideshow store error: ' . $e->getMessage());

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
            $slideshow = CmsSlideshow::where('etablissement_id', $etablissement->id)
                ->where('id', $id)
                ->firstOrFail();

            $data = $this->validatedData($request, false);
            $media = null;

            if ($request->hasFile('video_file') || $request->filled('video_url')) {
                $media = $this->resolveVideoSource($request, $etablissement);

                if ($slideshow->source === 'local' && $slideshow->video_path) {
                    Storage::disk('public')->delete($slideshow->video_path);
                }
            }

            $slideshow->fill([
                'title' => $data['title'] ?? null,
                'subtitle' => $data['subtitle'] ?? null,
                'poster_url' => $data['poster_url'] ?? null,
                'button_text' => $data['button_text'] ?? null,
                'button_url' => $data['button_url'] ?? null,
                'button_target' => $data['button_target'] ?? '_self',
                'is_active' => $request->boolean('is_active'),
                'options' => [
                    'autoplay' => $request->boolean('autoplay', true),
                    'muted' => $request->boolean('muted', true),
                    'loop' => $request->boolean('loop', true),
                ],
            ]);

            if ($media) {
                $slideshow->source = $media['source'];
                $slideshow->video_url = $media['video_url'];
                $slideshow->video_path = $media['video_path'];
            }

            $slideshow->save();

            return response()->json([
                'success' => true,
                'message' => 'Vidéo slideshow mise à jour avec succès',
                'data' => $this->mapSlideshow($slideshow->fresh()),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Slideshow update error: ' . $e->getMessage());

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
            $slideshow = CmsSlideshow::where('etablissement_id', $etablissement->id)
                ->where('id', $id)
                ->firstOrFail();

            if ($slideshow->source === 'local' && $slideshow->video_path) {
                Storage::disk('public')->delete($slideshow->video_path);
            }

            $slideshow->delete();
            $this->reorder($etablissement->id);

            return response()->json([
                'success' => true,
                'message' => 'Vidéo slideshow supprimée avec succès',
            ]);
        } catch (\Throwable $e) {
            Log::error('Slideshow delete error: ' . $e->getMessage());

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
            $slideshow = CmsSlideshow::where('etablissement_id', $etablissement->id)
                ->where('id', $id)
                ->firstOrFail();

            $slideshow->update(['is_active' => !$slideshow->is_active]);

            return response()->json([
                'success' => true,
                'message' => $slideshow->fresh()->is_active ? 'Vidéo activée' : 'Vidéo désactivée',
                'data' => $this->mapSlideshow($slideshow->fresh()),
            ]);
        } catch (\Throwable $e) {
            Log::error('Slideshow toggle error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut',
            ], 500);
        }
    }

    public function reorderItems(Request $request, $etablissementId): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);

            $request->validate([
                'orders' => 'required|array',
                'orders.*.id' => 'required|integer',
                'orders.*.sort_order' => 'required|integer|min:0',
            ]);

            foreach ($request->input('orders', []) as $item) {
                CmsSlideshow::where('etablissement_id', $etablissement->id)
                    ->where('id', $item['id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ordre mis à jour',
            ]);
        } catch (\Throwable $e) {
            Log::error('Slideshow reorder error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation',
            ], 500);
        }
    }

    private function validatedData(Request $request, bool $isCreate): array
    {
        $rules = [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:1000',
            'source' => 'required|in:local,url',
            'video_file' => 'nullable|file|mimes:mp4,mov,ogg,webm|max:102400',
            'video_url' => 'nullable|url|max:2048',
            'poster_url' => 'nullable|url|max:2048',
            'button_text' => 'nullable|string|max:120',
            'button_url' => 'nullable|string|max:2048',
            'button_target' => 'nullable|in:_self,_blank',
            'is_active' => 'nullable|boolean',
            'autoplay' => 'nullable|boolean',
            'muted' => 'nullable|boolean',
            'loop' => 'nullable|boolean',
        ];

        $data = $request->validate($rules);

        if ($isCreate && $request->input('source') === 'local' && !$request->hasFile('video_file')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'video_file' => 'La vidéo locale est obligatoire.',
            ]);
        }

        if ($isCreate && $request->input('source') === 'url' && !$request->filled('video_url')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'video_url' => 'L’URL de la vidéo est obligatoire.',
            ]);
        }

        if (!$isCreate && $request->input('source') === 'url' && $request->filled('video_url') === false && $request->hasFile('video_file') === false) {
            return $data;
        }

        return $data;
    }

    private function resolveVideoSource(Request $request, Etablissement $etablissement): array
    {
        if ($request->input('source') === 'url') {
            return [
                'source' => 'url',
                'video_url' => (string) $request->input('video_url'),
                'video_path' => null,
            ];
        }

        $file = $request->file('video_file');
        $directory = "slideshows/{$etablissement->id}/videos";
        $filename = $this->resolveOriginalFilename($file, $directory);
        $storedPath = $file->storeAs($directory, $filename, 'public');

        return [
            'source' => 'local',
            'video_url' => $this->toAbsolutePublicUrl(Storage::disk('public')->url($storedPath)),
            'video_path' => $storedPath,
        ];
    }

    private function mapSlideshow(CmsSlideshow $slideshow): array
    {
        return [
            'id' => $slideshow->id,
            'title' => $slideshow->title ?? '',
            'subtitle' => $slideshow->subtitle ?? '',
            'source' => $slideshow->source,
            'video_url' => $this->toAbsolutePublicUrl($slideshow->video_url),
            'video_path' => $slideshow->video_path,
            'poster_url' => $slideshow->poster_url ?? '',
            'button_text' => $slideshow->button_text ?? '',
            'button_url' => $slideshow->button_url ?? '',
            'button_target' => $slideshow->button_target ?: '_self',
            'is_active' => (bool) $slideshow->is_active,
            'sort_order' => (int) $slideshow->sort_order,
            'options' => $slideshow->options ?: [],
            'created_at' => optional($slideshow->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function nextOrder(int $etablissementId): int
    {
        return (int) CmsSlideshow::where('etablissement_id', $etablissementId)->max('sort_order') + 1;
    }

    private function reorder(int $etablissementId): void
    {
        CmsSlideshow::where('etablissement_id', $etablissementId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->values()
            ->each(fn (CmsSlideshow $slideshow, int $index) => $slideshow->update(['sort_order' => $index + 1]));
    }

    private function resolveOriginalFilename($file, string $directory): string
    {
        $originalName = trim((string) $file->getClientOriginalName());
        $originalName = basename(str_replace(['\\', '/'], '-', $originalName));
        $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?: '';

        if ($originalName === '' || $originalName === '.' || $originalName === '..') {
            $extension = $file->getClientOriginalExtension();
            $originalName = 'slideshow-' . Str::uuid() . ($extension ? '.' . $extension : '');
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
}
