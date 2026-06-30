<?php

namespace Vendor\Etablissement\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Vendor\Etablissement\Mail\WelcomeEtablissementMail;

class EtablissementController extends Controller
{
    private function getCurrentEtablissement(): ?Etablissement
    {
        if (auth()->check() && auth()->user()->etablissement) {
            return auth()->user()->etablissement;
        }
        return null;
    }

    public function index(Request $request)
    {
        $etablissement = $this->getCurrentEtablissement();

        if (!$etablissement) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun établissement associé à votre compte.',
                ], 404);
            }
            return view('etablissement::etablissements.index', [
                'etablissement' => null,
                'noEstablishment' => true,
            ]);
        }

        // Rediriger directement vers l'interface de gestion (CMS) de l'établissement connecté
        $slug = $etablissement->slug ?: Str::slug($etablissement->name);
        return redirect()->route('cms.admin.dashboard', [
            'etablissementId' => $etablissement->id,
            'slug' => $slug,
        ]);
    }

    private function attachCmsSiteSlugs(iterable $etablissements): void
    {
        $items = collect($etablissements);
        $ids = $items->pluck('id')->filter()->values();

        if ($ids->isEmpty()) {
            return;
        }

        $settingsByEtablissement = DB::connection('cms')
            ->table('cms_settings')
            ->select('etablissement_id', 'key', 'value')
            ->whereIn('etablissement_id', $ids)
            ->where('group', 'general')
            ->whereIn('key', ['site_name', 'name'])
            ->get()
            ->groupBy('etablissement_id');

        $items->each(function (Etablissement $etablissement) use ($settingsByEtablissement) {
            $settings = $settingsByEtablissement->get($etablissement->id, collect());
            $siteName = optional($settings->firstWhere('key', 'site_name'))->value
                ?: optional($settings->firstWhere('key', 'name'))->value
                ?: $etablissement->name;

            $siteSlug = Str::slug((string) $siteName)
                ?: ($etablissement->slug ?: Str::slug((string) $etablissement->name))
                ?: (string) $etablissement->id;

            $etablissement->setAttribute('cms_site_name', $siteName);
            $etablissement->setAttribute('cms_site_slug', $siteSlug);
        });
    }

    public function statistics()
    {
        $etablissement = $this->getCurrentEtablissement();

        if (!$etablissement) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun établissement associé à votre compte.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $etablissement->name,
                'ville' => $etablissement->ville,
                'is_active' => $etablissement->is_active,
                'phone' => $etablissement->phone,
                'email_contact' => $etablissement->email_contact,
                'activities_count' => $etablissement->activities()->count(),
                'total_activities' => DB::table('activity_etablissement')
                    ->where('etablissement_id', $etablissement->id)
                    ->count(),
            ]
        ]);
    }

    public function show()
    {
        $etablissement = $this->getCurrentEtablissement();

        if (!$etablissement) {
            return redirect()->route('etablissements.index')
                ->with('error', 'Aucun établissement associé à votre compte.');
        }

        $etablissement->load(['user', 'activities']);
        return view('etablissement::etablissements.show', compact('etablissement'));
    }

    public function edit()
    {
        $etablissement = $this->getCurrentEtablissement();

        if (!$etablissement) {
            return redirect()->route('etablissements.index')
                ->with('error', 'Aucun établissement associé à votre compte.');
        }

        $etablissement->load(['user.roles', 'activities']);
        $roles = Role::all();
        $users = User::all();
        $activities = \App\Models\Activity::all();
        $selectedActivities = $etablissement->activities->pluck('id')->toArray();
        $linkedUser = $etablissement->user;
        $shouldShowLoginFields = (bool) ($linkedUser || old('create_login_account', false));

        return view('etablissement::etablissements.edit', compact(
            'etablissement', 'roles', 'users', 'activities',
            'selectedActivities', 'linkedUser', 'shouldShowLoginFields'
        ));
    }

    public function update(Request $request)
    {
        $etablissement = $this->getCurrentEtablissement();

        if (!$etablissement) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun établissement associé à votre compte.',
                ], 404);
            }
            return redirect()->route('etablissements.index')
                ->with('error', 'Aucun établissement associé à votre compte.');
        }

        $user = $etablissement->user;
        $shouldManageAccount = $this->shouldManageUserAccount($request, $user);

        $this->normalizeOptionalAccountRequest($request, $shouldManageAccount);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email_contact' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'ville_search' => 'nullable|string|max:255',
            'ville' => 'required|string|max:255',
            'region_id' => 'nullable|exists:regions,id',
            'province_id' => 'required|exists:provinces,id',
            'country_id' => 'required|exists:countries,id',
            'adresse' => 'required|string',
            'zip_code' => 'required|string|max:10',
            'primary_activity_id' => 'nullable|exists:activities,id',
            'other_activity_label' => 'nullable|string|max:255',
            'activities' => 'nullable|array',
            'activities.*' => 'exists:activities,id',
            'create_login_account' => 'nullable|boolean',
            'user_name' => $shouldManageAccount ? 'required|string|max:255' : 'nullable|string|max:255',
            'user_email' => $shouldManageAccount
                ? ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)]
                : ['nullable', 'email', 'max:255'],
            'password' => ($shouldManageAccount && ! $user ? 'required' : 'nullable') . '|string|min:8|confirmed',
            'role_id' => $shouldManageAccount ? 'required|exists:roles,id' : 'nullable|exists:roles,id',
            'is_active' => 'sometimes',
        ], $this->etablissementValidationMessages());

        $this->normalizeActivityPayload($validated);

        return DB::transaction(function () use ($request, $validated, $etablissement, $user, $shouldManageAccount) {
            $targetUser = $user;

            if ($shouldManageAccount) {
                if (! $targetUser) {
                    $targetUser = User::create([
                        'name' => $validated['user_name'],
                        'email' => $validated['user_email'],
                        'password' => Hash::make($validated['password']),
                    ]);

                    try {
                        $this->sendWelcomeEmail($targetUser, $validated['password'], $etablissement);
                    } catch (\Throwable $emailException) {
                        Log::warning('Impossible d\'envoyer l\'email de bienvenue après création du compte client', [
                            'user_id' => $targetUser->id,
                            'error' => $emailException->getMessage(),
                        ]);
                    }
                } else {
                    $targetUser->name = $validated['user_name'];
                    $targetUser->email = $validated['user_email'];

                    if ($request->filled('password')) {
                        $targetUser->password = Hash::make($validated['password']);
                    }

                    $targetUser->save();
                }

                $role = Role::findOrFail($validated['role_id']);
                $targetUser->syncRoles([$role]);
            }

            $etablissement->update([
                'name' => $validated['name'],
                'lname' => $shouldManageAccount ? ($validated['user_name'] ?? $etablissement->lname) : $etablissement->lname,
                'ville' => $validated['ville'],
                'user_id' => $targetUser?->id,
                'adresse' => $validated['adresse'],
                'zip_code' => $validated['zip_code'],
                'phone' => $validated['phone'] ?? null,
                'fax' => $validated['fax'] ?? null,
                'email_contact' => $validated['email_contact'] ?? null,
                'website' => $validated['website'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'region_id' => $validated['region_id'] ?? null,
                'province_id' => $validated['province_id'],
                'country_id' => $validated['country_id'],
                'primary_activity_id' => $validated['primary_activity_id'] ?? null,
                'other_activity_label' => $validated['other_activity_label'] ?? null,
            ]);

            $this->syncEtablissementActivities(
                $etablissement,
                $validated['activities'] ?? [],
                isset($validated['primary_activity_id']) ? (int) $validated['primary_activity_id'] : null
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Établissement mis à jour avec succès.',
                    'data' => $etablissement->load('user.roles', 'activities', 'region', 'province', 'country'),
                ]);
            }

            return redirect()->route('etablissements.index')
                ->with('success', 'Établissement mis à jour avec succès.');
        });
    }

    private function buildSlugFromName(?string $name): ?string
    {
        $slug = Str::slug((string) $name);
        return $slug !== '' ? $slug : null;
    }

    private function sendWelcomeEmail($user, $password, $etablissement)
    {
        Mail::to($user->email)
            ->send(new WelcomeEtablissementMail($user, $password, $etablissement));
    }

    private function shouldManageUserAccount(Request $request, ?User $existingUser = null): bool
    {
        return $existingUser !== null
            || $request->boolean('create_login_account');
    }

    private function normalizeOptionalAccountRequest(Request $request, bool $shouldManageAccount): void
    {
        if ($shouldManageAccount) {
            return;
        }

        $request->merge([
            'user_name' => null,
            'user_email' => null,
            'password' => null,
            'password_confirmation' => null,
            'role_id' => null,
        ]);
    }

    private function syncEtablissementActivities(Etablissement $etablissement, array $activityIds = [], ?int $primaryActivityId = null): void
    {
        $ids = collect($activityIds)
            ->filter()
            ->map(fn ($id) => (int) $id);

        if ($primaryActivityId) {
            $ids->prepend($primaryActivityId);
        }

        $ids = $ids->unique()->values()->all();

        if ($ids === []) {
            $etablissement->activities()->detach();
            return;
        }

        $etablissement->activities()->sync($ids);
    }

    private function normalizeActivityPayload(array &$validated): void
    {
        $validated['primary_activity_id'] = !empty($validated['primary_activity_id'])
            ? (int) $validated['primary_activity_id']
            : null;

        $validated['other_activity_label'] = isset($validated['other_activity_label'])
            ? (trim((string) $validated['other_activity_label']) ?: null)
            : null;

        $validated['activities'] = collect($validated['activities'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($validated['primary_activity_id']) && $validated['activities'] !== []) {
            $validated['primary_activity_id'] = $validated['activities'][0];
        }

        if (
            empty($validated['primary_activity_id'])
            && empty($validated['other_activity_label'])
            && $validated['activities'] === []
        ) {
            throw ValidationException::withMessages([
                'primary_activity_id' => 'Veuillez choisir une activité principale ou renseigner une autre activité.',
                'other_activity_label' => 'Veuillez renseigner une autre activité si aucune activité existante ne convient.',
            ]);
        }
    }

    private function etablissementValidationMessages(): array
    {
        return [
            'name.required' => 'Le nom de l\'établissement est obligatoire.',
            'name.max' => 'Le nom de l\'établissement ne doit pas dépasser 255 caractères.',
            'email_contact.email' => 'L\'email de contact doit être valide.',
            'ville.required' => 'Veuillez sélectionner une ville valide.',
            'province_id.required' => 'La province est obligatoire.',
            'province_id.exists' => 'La province sélectionnée est invalide.',
            'country_id.required' => 'Le pays est obligatoire.',
            'country_id.exists' => 'Le pays sélectionné est invalide.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'zip_code.required' => 'Le code postal est obligatoire.',
            'zip_code.max' => 'Le code postal ne doit pas dépasser 10 caractères.',
            'primary_activity_id.exists' => 'L\'activité principale sélectionnée est invalide.',
            'activities.array' => 'Les activités sélectionnées sont invalides.',
            'activities.*.exists' => 'Une activité sélectionnée est invalide.',
            'user_name.required' => 'Le nom du compte client est obligatoire.',
            'user_email.required' => 'L\'email du compte client est obligatoire.',
            'user_email.email' => 'L\'email du compte client doit être valide.',
            'user_email.unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role_id.required' => 'Veuillez choisir un rôle pour le compte client.',
            'role_id.exists' => 'Le rôle sélectionné est invalide.',
        ];
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit', 20);

        $results = collect();

        try {
            if (\Schema::hasTable('villes')) {
                $villes = \App\Models\Ville::with(['region', 'province', 'country'])
                    ->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('region', function($query) use ($search) {
                        $query->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('province', function($query) use ($search) {
                        $query->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('country', function($query) use ($search) {
                        $query->where('name', 'LIKE', "%{$search}%");
                    })
                    ->limit($limit)
                    ->get()
                    ->map(function($ville) {
                        return [
                            'id' => $ville->id,
                            'name' => $ville->name,
                            'type' => 'city',
                            'region_id' => $ville->region_id,
                            'region_name' => $ville->region->name ?? null,
                            'province_id' => $ville->province_id,
                            'province_name' => $ville->province->name ?? null,
                            'country_id' => $ville->country_id,
                            'country_name' => $ville->country->name ?? null,
                            'zip_code' => $ville->zip_code ?? null,
                            'source' => 'villes'
                        ];
                    });

                $results = $results->merge($villes);
            }
        } catch (\Exception $e) {
            \Log::warning('Table villes non disponible: ' . $e->getMessage());
        }

        if ($results->count() < $limit / 2) {
            try {
                if (\Schema::hasTable('regions')) {
                    $regions = \App\Models\Region::with(['province', 'province.country'])
                        ->where('name', 'LIKE', "%{$search}%")
                        ->limit($limit - $results->count())
                        ->get()
                        ->map(function($region) {
                            return [
                                'id' => $region->id,
                                'name' => $region->name,
                                'type' => 'region',
                                'region_id' => $region->id,
                                'region_name' => $region->name,
                                'province_id' => $region->province_id,
                                'province_name' => $region->province->name ?? null,
                                'country_id' => $region->province->country_id ?? null,
                                'country_name' => $region->province->country->name ?? null,
                                'zip_code' => null,
                                'source' => 'regions'
                            ];
                        });

                    $results = $results->merge($regions);
                }
            } catch (\Exception $e) {
                \Log::warning('Table regions non disponible: ' . $e->getMessage());
            }
        }

        if ($results->count() < $limit / 2) {
            try {
                if (\Schema::hasTable('provinces')) {
                    $provinces = \App\Models\Province::with(['country'])
                        ->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%")
                        ->limit($limit - $results->count())
                        ->get()
                        ->map(function($province) {
                            return [
                                'id' => $province->id,
                                'name' => $province->name,
                                'type' => 'province',
                                'region_id' => null,
                                'region_name' => null,
                                'province_id' => $province->id,
                                'province_name' => $province->name,
                                'province_code' => $province->code,
                                'country_id' => $province->country_id,
                                'country_name' => $province->country->name ?? null,
                                'country_code' => $province->country->code ?? null,
                                'zip_code' => null,
                                'source' => 'provinces'
                            ];
                        });

                    $results = $results->merge($provinces);
                }
            } catch (\Exception $e) {
                \Log::warning('Table provinces non disponible: ' . $e->getMessage());
            }
        }

        if ($results->count() < $limit / 2) {
            try {
                if (\Schema::hasTable('countries')) {
                    $countries = \App\Models\Country::where('name', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%")
                        ->limit($limit - $results->count())
                        ->get()
                        ->map(function($country) {
                            return [
                                'id' => $country->id,
                                'name' => $country->name,
                                'type' => 'country',
                                'region_id' => null,
                                'region_name' => null,
                                'province_id' => null,
                                'province_name' => null,
                                'country_id' => $country->id,
                                'country_name' => $country->name,
                                'country_code' => $country->code,
                                'zip_code' => null,
                                'source' => 'countries'
                            ];
                        });

                    $results = $results->merge($countries);
                }
            } catch (\Exception $e) {
                \Log::warning('Table countries non disponible: ' . $e->getMessage());
            }
        }

        if ($results->isEmpty()) {
            $results = collect([
                [
                    'id' => 1,
                    'name' => 'Québec',
                    'type' => 'region',
                    'region_id' => 1,
                    'region_name' => 'Québec',
                    'province_id' => 1,
                    'province_name' => 'Québec',
                    'province_code' => 'QC',
                    'country_id' => 1,
                    'country_name' => 'Canada',
                    'country_code' => 'CA',
                    'zip_code' => null,
                    'source' => 'default'
                ],
                [
                    'id' => 2,
                    'name' => 'Montréal',
                    'type' => 'city',
                    'region_id' => 1,
                    'region_name' => 'Québec',
                    'province_id' => 1,
                    'province_name' => 'Québec',
                    'province_code' => 'QC',
                    'country_id' => 1,
                    'country_name' => 'Canada',
                    'country_code' => 'CA',
                    'zip_code' => 'H3A 1A1',
                    'source' => 'default'
                ],
                [
                    'id' => 3,
                    'name' => 'Toronto',
                    'type' => 'city',
                    'region_id' => 2,
                    'region_name' => 'Ontario',
                    'province_id' => 2,
                    'province_name' => 'Ontario',
                    'province_code' => 'ON',
                    'country_id' => 1,
                    'country_name' => 'Canada',
                    'country_code' => 'CA',
                    'zip_code' => 'M5H 2N2',
                    'source' => 'default'
                ]
            ]);
        }

        $sortedResults = $results->sortBy(function($item) {
            $priority = [
                'city' => 1,
                'region' => 2,
                'province' => 3,
                'country' => 4,
                'default' => 5
            ];
            return $priority[$item['type']] ?? 6;
        })->values();

        $finalResults = $sortedResults->take($limit);

        return response()->json([
            'success' => true,
            'villes' => $finalResults,
            'sources' => $results->pluck('source')->unique()->values(),
            'count' => $finalResults->count(),
            'total' => $results->count()
        ]);
    }

    public function getActivities()
    {
        $activities = \App\Models\Activity::with('categoryRelation:id,name')
            ->select('id', 'name', 'categorie_id', 'description')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'activities' => $activities->map(fn ($activity) => [
                'id' => $activity->id,
                'name' => $activity->name,
                'categorie_id' => $activity->categorie_id,
                'description' => $activity->description,
                'category' => $activity->categoryRelation?->name,
            ])->values(),
        ]);
    }
}
