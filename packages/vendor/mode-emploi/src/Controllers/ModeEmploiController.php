<?php

namespace Vendor\ModeEmploi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ModeEmploiLesson;
use App\Models\ModeEmploiModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ModeEmploiController extends Controller
{
    public function index(Request $request)
    {
        $modules = ModeEmploiModule::with(['lessons' => function ($q) {
            $q->orderBy('order');
        }])->orderBy('order')->get();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $modules,
            ]);
        }

        return view('mode-emploi::index', compact('modules'));
    }

    // ─── MODULES ───────────────────────────────────────────────

    public function storeModule(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->exists('is_active');

        $module = ModeEmploiModule::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Module créé avec succès!',
            'data' => $module->load('lessons'),
        ]);
    }

    public function updateModule(Request $request, ModeEmploiModule $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ]);

        if ($request->exists('is_active')) {
            $validated['is_active'] = true;
        }

        $module->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Module mis à jour avec succès!',
            'data' => $module->fresh()->load('lessons'),
        ]);
    }

    public function destroyModule(ModeEmploiModule $module)
    {
        $module->delete();

        return response()->json([
            'success' => true,
            'message' => 'Module supprimé avec succès!',
        ]);
    }

    // ─── LESSONS ───────────────────────────────────────────────

    public function storeLesson(Request $request)
    {
        $rules = [
            'module_id' => 'required|exists:mode_emploi_modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'video_path' => 'nullable|string|max:500',
            'video_provider' => 'nullable|string|in:youtube,local',
            'duration_minutes' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
            'video_file' => 'nullable|mimes:mp4,mov,avi,wmv,webm|max:1004800',
        ];

        $request->validate($rules);

        $validated = $request->only([
            'module_id', 'title', 'description', 'video_url',
            'video_path', 'video_provider', 'duration_minutes', 'order',
        ]);

        $validated['is_active'] = $request->exists('is_active');
        $validated['video_provider'] = $validated['video_provider'] ?? 'local';

        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('mode-emploi-videos', 'public');
            $validated['video_path'] = $path;
            if (empty($validated['video_url'])) {
                $validated['video_url'] = null;
            }
        } elseif (!$request->filled('video_path')) {
            $validated['video_path'] = null;
        }

        $lesson = ModeEmploiLesson::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Leçon créée avec succès!',
            'data' => $lesson->load('module'),
        ]);
    }

    public function updateLesson(Request $request, ModeEmploiLesson $lesson)
    {
        $rules = [
            'module_id' => 'required|exists:mode_emploi_modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'video_path' => 'nullable|string|max:500',
            'video_provider' => 'nullable|string|in:youtube,local',
            'duration_minutes' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
            'video_file' => 'nullable|mimes:mp4,mov,avi,wmv,webm|max:204800',
        ];

        $request->validate($rules);

        $validated = $request->only([
            'module_id', 'title', 'description', 'video_url',
            'video_path', 'video_provider', 'duration_minutes', 'order',
        ]);

        if ($request->exists('is_active')) {
            $validated['is_active'] = true;
        }
        $validated['video_provider'] = $validated['video_provider'] ?? 'local';

        if ($request->hasFile('video_file')) {
            if ($lesson->video_path && !str_starts_with($lesson->video_path, 'http://') && !str_starts_with($lesson->video_path, 'https://')) {
                Storage::disk('public')->delete($lesson->video_path);
            }
            $path = $request->file('video_file')->store('mode-emploi-videos', 'public');
            $validated['video_path'] = $path;
            if (empty($validated['video_url'])) {
                $validated['video_url'] = null;
            }
        } elseif (!$request->filled('video_path')) {
            $validated['video_path'] = null;
        }

        $lesson->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Leçon mise à jour avec succès!',
            'data' => $lesson->fresh()->load('module'),
        ]);
    }

    public function destroyLesson(ModeEmploiLesson $lesson)
    {
        if ($lesson->video_path && !str_starts_with($lesson->video_path, 'http://') && !str_starts_with($lesson->video_path, 'https://')) {
            Storage::disk('public')->delete($lesson->video_path);
        }

        $lesson->delete();

        return response()->json([
            'success' => true,
            'message' => 'Leçon supprimée avec succès!',
        ]);
    }
}
