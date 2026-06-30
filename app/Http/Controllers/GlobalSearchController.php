<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['success' => true, 'results' => []]);
        }

        $results = [];

        // Search Etablissements
        $etablissements = Etablissement::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('lname', 'like', "%{$query}%")
                  ->orWhere('ville', 'like', "%{$query}%");
            })
            ->limit(3)
            ->get();
        foreach ($etablissements as $etablissement) {
            $results[] = [
                'type' => 'etablissement',
                'title' => $etablissement->name,
                'subtitle' => $etablissement->ville . ' - ' . $etablissement->zip_code,
                'url' => route('etablissements.index'),
                'badge' => $etablissement->is_active ? 'Actif' : 'Inactif',
            ];
        }

        // Search Projects
        $projects = Project::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get();
        foreach ($projects as $project) {
            $results[] = [
                'type' => 'project',
                'title' => $project->name,
                'subtitle' => $project->status,
                'url' => route('projects.show', $project->id),
                'badge' => $project->progress . '%',
            ];
        }

        // Search Tasks
        $tasks = Task::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get();
        foreach ($tasks as $task) {
            $results[] = [
                'type' => 'task',
                'title' => $task->name,
                'subtitle' => $task->project->name ?? '',
                'url' => route('tasks.show', $task->id),
                'badge' => $task->status,
            ];
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'total' => count($results),
        ]);
    }
}
