<?php

namespace Vendor\Administration\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PlanServiceController extends Controller
{
    private function toFullStorageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return $path;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $normalized = ltrim($path, '/');
        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        return asset('storage/' . $normalized);
    }

    public function index(Request $request)
    {
        $plans = Plan::orderBy('name')->get(['id', 'name']);
        $selectedPlanId = $request->integer('plan_id');

        $query = PlanService::with('plan')->orderByDesc('id');
        if ($selectedPlanId) {
            $query->where('plan_id', $selectedPlanId);
        }

        $services = $query->paginate(30);

        return view('administration::plan-services.index', compact('plans', 'selectedPlanId', 'services'));
    }

    public function create(Request $request)
    {
        $plans = Plan::orderBy('name')->get(['id', 'name']);
        $selectedPlanId = $request->integer('plan_id');

        return view('administration::plan-services.create', compact('plans', 'selectedPlanId'));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('plan_services')) {
            return response()->json([
                'success' => false,
                'message' => "La table 'plan_services' n'existe pas. Executez: php artisan migrate"
            ], 500);
        }

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'service_type' => 'required|in:free,paid',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'is_active' => 'nullable|boolean',
            'main_media_type' => 'required|in:image,video_upload,video_url',
            'main_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif|max:5120',
            'main_video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'main_video_url' => 'nullable|url|max:2000',
            'gallery_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif|max:5120',
            'gallery_videos.*' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'delete_gallery_indices' => 'nullable|array',
            'delete_gallery_indices.*' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $serviceType = $request->input('service_type', 'free');
        $price = $serviceType === 'paid' ? (float) $request->input('price', 0) : 0;

        $mainImagePath = null;
        $mainVideoPath = null;
        $mainVideoUrl = null;

        if ($request->main_media_type === 'image' && $request->hasFile('main_image')) {
            $mainImagePath = $this->toFullStorageUrl(
                $request->file('main_image')->store('plan-services/main', 'public')
            );
        }

        if ($request->main_media_type === 'video_upload' && $request->hasFile('main_video')) {
            $mainVideoPath = $this->toFullStorageUrl(
                $request->file('main_video')->store('plan-services/main', 'public')
            );
        }

        if ($request->main_media_type === 'video_url') {
            $mainVideoUrl = $request->input('main_video_url');
        }

        $gallery = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $img) {
                $gallery[] = [
                    'type' => 'image',
                    'path' => $this->toFullStorageUrl(
                        $img->store('plan-services/gallery/images', 'public')
                    ),
                ];
            }
        }
        if ($request->hasFile('gallery_videos')) {
            foreach ($request->file('gallery_videos') as $vid) {
                $gallery[] = [
                    'type' => 'video',
                    'path' => $this->toFullStorageUrl(
                        $vid->store('plan-services/gallery/videos', 'public')
                    ),
                ];
            }
        }

        try {
            $service = PlanService::create([
                'plan_id' => $request->integer('plan_id'),
                'title' => $request->input('title'),
                'slug' => Str::slug($request->input('title')),
                'description' => $request->input('description'),
                'content' => $request->input('content'),
                'service_type' => $serviceType,
                'price' => $price,
                'currency' => strtoupper($request->input('currency', 'CAD')),
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => 0,
                'main_media_type' => $request->input('main_media_type'),
                'main_image_path' => $mainImagePath,
                'main_video_path' => $mainVideoPath,
                'main_video_url' => $mainVideoUrl,
                'gallery' => $gallery,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur base de donnees lors de la creation du service: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Service cree avec succes.',
            'service_id' => $service->id,
            'redirect' => route('plan-services.edit', $service->id),
        ]);
    }

    public function edit(int $id)
    {
        $service = PlanService::findOrFail($id);
        $plans = Plan::orderBy('name')->get(['id', 'name']);

        return view('administration::plan-services.edit', compact('service', 'plans'));
    }

    public function update(Request $request, int $id)
    {
        $service = PlanService::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'service_type' => 'required|in:free,paid',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'is_active' => 'nullable|boolean',
            'main_media_type' => 'required|in:image,video_upload,video_url',
            'main_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif|max:5120',
            'main_video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'main_video_url' => 'nullable|url|max:2000',
            'gallery_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif|max:5120',
            'gallery_videos.*' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $serviceType = $request->input('service_type', 'free');
        $price = $serviceType === 'paid' ? (float) $request->input('price', 0) : 0;

        $mainImagePath = $this->toFullStorageUrl($service->main_image_path);
        $mainVideoPath = $this->toFullStorageUrl($service->main_video_path);
        $mainVideoUrl = $service->main_video_url;

        if ($request->main_media_type === 'image' && $request->hasFile('main_image')) {
            $mainImagePath = $this->toFullStorageUrl(
                $request->file('main_image')->store('plan-services/main', 'public')
            );
            $mainVideoPath = null;
            $mainVideoUrl = null;
        }

        if ($request->main_media_type === 'video_upload' && $request->hasFile('main_video')) {
            $mainVideoPath = $this->toFullStorageUrl(
                $request->file('main_video')->store('plan-services/main', 'public')
            );
            $mainImagePath = null;
            $mainVideoUrl = null;
        }

        if ($request->main_media_type === 'video_url') {
            $mainVideoUrl = $request->input('main_video_url');
            $mainImagePath = null;
            $mainVideoPath = null;
        }

        $gallery = is_array($service->gallery) ? $service->gallery : [];

        // Remove selected existing gallery items by index from edit screen.
        $deleteIndices = collect($request->input('delete_gallery_indices', []))
            ->filter(fn ($v) => is_numeric($v))
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        foreach ($deleteIndices as $idx) {
            if (array_key_exists($idx, $gallery)) {
                unset($gallery[$idx]);
            }
        }
        $gallery = array_values($gallery);

        foreach ($gallery as $k => $item) {
            if (is_array($item) && isset($item['path'])) {
                $gallery[$k]['path'] = $this->toFullStorageUrl($item['path']);
            }
        }
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $img) {
                $gallery[] = [
                    'type' => 'image',
                    'path' => $this->toFullStorageUrl(
                        $img->store('plan-services/gallery/images', 'public')
                    ),
                ];
            }
        }
        if ($request->hasFile('gallery_videos')) {
            foreach ($request->file('gallery_videos') as $vid) {
                $gallery[] = [
                    'type' => 'video',
                    'path' => $this->toFullStorageUrl(
                        $vid->store('plan-services/gallery/videos', 'public')
                    ),
                ];
            }
        }

        $service->update([
            'plan_id' => $request->integer('plan_id'),
            'title' => $request->input('title'),
            // 'slug' => Str::slug($request->input('title')),
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'service_type' => $serviceType,
            'price' => $price,
            'currency' => strtoupper($request->input('currency', 'CAD')),
            'is_active' => $request->boolean('is_active', true),
            'main_media_type' => $request->input('main_media_type'),
            'main_image_path' => $mainImagePath,
            'main_video_path' => $mainVideoPath,
            'main_video_url' => $mainVideoUrl,
            'gallery' => $gallery,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service modifie avec succes.',
            'redirect' => route('plan-services.index', ['plan_id' => $service->plan_id]),
        ]);
    }

    public function destroy(int $id)
    {
        $service = PlanService::findOrFail($id);
        $service->delete();

        return response()->json(['success' => true, 'message' => 'Service supprime avec succes.']);
    }
}
