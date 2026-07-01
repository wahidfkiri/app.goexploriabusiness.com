<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Etablissement;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Template;
use Illuminate\Http\Request;

class HomeController extends Controller
{

public function __construct()
{
    $this->middleware(['auth', 'user.active']);
}
    public function index()
    {
        $user = auth()->user();
        $isScoped = $user->hasRole('entreprise') || $user->hasRole('partenaire-affilie');
        $etablissement = null;

        if ($isScoped) {
            $etablissement = $user->etablissement;
            $etablissementId = $etablissement?->id;

            $totalPays = $etablissement?->country_id ? 1 : 0;
            $totalEtablissements = $etablissement ? 1 : 0;
            $totalProjetsEnCours = $etablissementId
                ? Project::where('etablissement_id', $etablissementId)->whereIn('status', ['en_cours', 'active'])->count()
                : 0;
            $totalTasks = $etablissementId
                ? Task::where('etablissement_id', $etablissementId)->count()
                : 0;
            $totalUsers = $etablissement ? 1 : 0;
            $totalTemplates = Template::count();

            $projects = $etablissementId
                ? Project::with(['etablissement', 'user'])
                    ->where('etablissement_id', $etablissementId)
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get()
                : collect();
        } else {
            $totalPays = Country::count();
            $totalEtablissements = Etablissement::count();
            $totalProjetsEnCours = Project::whereIn('status', ['en_cours', 'active'])->count();
            $totalTasks = Task::count();
            $totalUsers = User::count();
            $totalTemplates = Template::count();

            $projects = Project::with(['etablissement', 'user'])
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
        }

        $recentActivities = $this->getRecentActivities();

        return view('home', compact(
            'totalPays',
            'totalEtablissements',
            'totalProjetsEnCours',
            'totalTasks',
            'totalUsers',
            'totalTemplates',
            'recentActivities',
            'projects',
            'etablissement'
        ));
    }
    
    private function getRecentActivities()
    {
        // Logique pour récupérer les activités récentes
        // Vous pouvez créer une table activities ou combiner plusieurs sources
        
        return collect([
            // Ces données devraient venir de votre base de données
            // Exemple avec des modèles différents
        ]);
    }
}