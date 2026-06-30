<?php

namespace Vendor\Etablissement\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Vendor\Etablissement\Models\RendezVous;
use Vendor\Etablissement\Services\CountryCalendarEventResolver;

class RendezVousController extends Controller
{
    public function __construct(
        private readonly CountryCalendarEventResolver $countryCalendarEventResolver
    ) {
    }

    public function index()
    {
        $etablissements = Etablissement::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'ville', 'email_contact', 'phone']);

        return view('etablissement::etablissements.rendezvous', [
            'etablissements' => $etablissements,
            'statusOptions' => $this->statusOptions(),
            'meetingTypeOptions' => $this->meetingTypeOptions(),
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $rendezVousEvents = $this->buildFilteredQuery($request)
            ->with(['etablissement:id,name,ville,email_contact,phone,country_id'])
            ->orderBy('starts_at')
            ->get()
            ->map(fn (RendezVous $rendezVous) => $this->formatRendezVousEvent($rendezVous));

        $countryEvents = $this->resolveCountryEvents($request)
            ->map(fn (array $occurrence) => $this->formatCountryCalendarEvent($occurrence));

        $events = $rendezVousEvents
            ->concat($countryEvents)
            ->sortBy(fn (array $event) => ($event['start'] ?? '') . '|' . ($event['title'] ?? ''))
            ->values();

        return response()->json($events);
    }

    public function statistics(Request $request): JsonResponse
    {
        $query = $this->buildFilteredQuery($request);
        $countryEvents = $this->resolveCountryEvents($request);

        $now = now();
        $stats = [
            'total' => (clone $query)->count(),
            'today' => (clone $query)
                ->whereDate('starts_at', $now->toDateString())
                ->count(),
            'confirmed' => (clone $query)
                ->where('status', 'confirmed')
                ->count(),
            'upcoming' => (clone $query)
                ->where('starts_at', '>', $now)
                ->whereIn('status', ['planned', 'confirmed', 'rescheduled'])
                ->count(),
            'cancelled' => (clone $query)
                ->where('status', 'cancelled')
                ->count(),
            'country_events' => $countryEvents->count(),
        ];

        if ($countryEvents->isNotEmpty()) {
            $todayStart = $now->copy()->startOfDay();
            $todayEnd = $now->copy()->endOfDay();

            $stats['total'] += $countryEvents->count();
            $stats['today'] += $countryEvents->filter(function (array $occurrence) use ($todayStart, $todayEnd) {
                return $occurrence['starts_at']->lessThanOrEqualTo($todayEnd)
                    && $occurrence['ends_at']->greaterThanOrEqualTo($todayStart);
            })->count();
            $stats['upcoming'] += $countryEvents->filter(
                fn (array $occurrence) => $occurrence['starts_at']->greaterThan($now)
            )->count();
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function searchEtablissements(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));
        $limit = max(1, min((int) $request->input('limit', 12), 25));

        $query = Etablissement::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'ville', 'email_contact', 'phone', 'adresse', 'country_id']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('ville', 'like', '%' . $search . '%')
                    ->orWhere('email_contact', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('adresse', 'like', '%' . $search . '%');
            });
        }

        $etablissements = $query
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Etablissement $etablissement) => [
                'id' => $etablissement->id,
                'name' => $etablissement->name,
                'ville' => $etablissement->ville,
                'email_contact' => $etablissement->email_contact,
                'phone' => $etablissement->phone,
                'adresse' => $etablissement->adresse,
                'country_id' => $etablissement->country_id,
                'label' => collect([$etablissement->name, $etablissement->ville])
                    ->filter()
                    ->implode(' - '),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $etablissements,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $rendezVous = RendezVous::query()
            ->with(['etablissement:id,name,ville,email_contact,phone,country_id'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatRendezVousRecord($rendezVous),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $this->validatedPayload($request);
        $payload['created_by'] = Auth::id();

        $rendezVous = RendezVous::create($payload)
            ->load(['etablissement:id,name,ville,email_contact,phone,country_id']);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous cree avec succes.',
            'data' => $this->formatRendezVousRecord($rendezVous),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $rendezVous = RendezVous::findOrFail($id);
        $payload = $this->validatedPayload($request);

        $rendezVous->update($payload);
        $rendezVous->load(['etablissement:id,name,ville,email_contact,phone,country_id']);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous mis a jour avec succes.',
            'data' => $this->formatRendezVousRecord($rendezVous),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $rendezVous = RendezVous::findOrFail($id);
        $rendezVous->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous supprime avec succes.',
        ]);
    }

    public function move(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'all_day' => ['nullable', 'boolean'],
        ]);

        $rendezVous = RendezVous::findOrFail($id);
        $allDay = (bool) ($validated['all_day'] ?? $rendezVous->all_day);
        [$startsAt, $endsAt] = $this->normalizeDates($validated['starts_at'], $validated['ends_at'] ?? null, $allDay);

        $rendezVous->update([
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'all_day' => $allDay,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Planning mis a jour.',
            'data' => $this->formatRendezVousRecord(
                $rendezVous->fresh(['etablissement:id,name,ville,email_contact,phone,country_id'])
            ),
        ]);
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = RendezVous::query();

        $start = $request->filled('start') ? Carbon::parse($request->input('start'))->startOfDay() : null;
        $end = $request->filled('end') ? Carbon::parse($request->input('end'))->endOfDay() : null;

        if ($start && $end) {
            $query->where(function ($builder) use ($start, $end) {
                $builder
                    ->whereBetween('starts_at', [$start, $end])
                    ->orWhereBetween('ends_at', [$start, $end])
                    ->orWhere(function ($inner) use ($start, $end) {
                        $inner->where('starts_at', '<=', $start)
                            ->where(function ($nested) use ($end) {
                                $nested->whereNull('ends_at')
                                    ->orWhere('ends_at', '>=', $end);
                            });
                    });
            });
        }

        if ($request->filled('etablissement_id')) {
            $query->where('etablissement_id', (int) $request->input('etablissement_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('meeting_type')) {
            $query->where('meeting_type', $request->input('meeting_type'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('contact_name', 'like', '%' . $search . '%')
                    ->orWhere('contact_email', 'like', '%' . $search . '%')
                    ->orWhere('contact_phone', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhereHas('etablissement', function ($etablissementQuery) use ($search) {
                        $etablissementQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('ville', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query;
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'etablissement_id' => ['required', 'exists:etablissements,id'],
            'title' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'all_day' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(array_keys($this->statusOptions()))],
            'meeting_type' => ['nullable', Rule::in(array_keys($this->meetingTypeOptions()))],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'color' => ['nullable', 'regex:/^#?[A-Fa-f0-9]{6}$/'],
        ]);

        $allDay = (bool) ($validated['all_day'] ?? false);
        [$startsAt, $endsAt] = $this->normalizeDates(
            $validated['starts_at'],
            $validated['ends_at'] ?? null,
            $allDay
        );

        $validated['starts_at'] = $startsAt;
        $validated['ends_at'] = $endsAt;
        $validated['all_day'] = $allDay;

        if (!empty($validated['color']) && !str_starts_with($validated['color'], '#')) {
            $validated['color'] = '#' . $validated['color'];
        }

        return $validated;
    }

    private function normalizeDates(string $startsAt, ?string $endsAt, bool $allDay): array
    {
        $start = Carbon::parse($startsAt);
        $end = $endsAt ? Carbon::parse($endsAt) : null;

        if ($allDay) {
            $start = $start->startOfDay();
            $end = $end ? $end->endOfDay() : $start->copy()->endOfDay();
        } elseif (!$end) {
            $end = $start->copy()->addHour();
        }

        return [$start, $end];
    }

    private function formatRendezVousEvent(RendezVous $rendezVous): array
    {
        $record = $this->formatRendezVousRecord($rendezVous);
        $eventEnd = $rendezVous->ends_at;

        if ($rendezVous->all_day && $eventEnd) {
            $eventEnd = $eventEnd->copy()->addDay()->startOfDay();
        }

        return [
            'id' => (string) $rendezVous->id,
            'title' => $rendezVous->title,
            'start' => $rendezVous->starts_at?->toIso8601String(),
            'end' => $eventEnd?->toIso8601String(),
            'allDay' => (bool) $rendezVous->all_day,
            'backgroundColor' => $rendezVous->resolved_color,
            'borderColor' => $rendezVous->resolved_color,
            'textColor' => '#ffffff',
            'editable' => true,
            'startEditable' => true,
            'durationEditable' => true,
            'extendedProps' => $record,
        ];
    }

    private function formatCountryCalendarEvent(array $occurrence): array
    {
        $record = $this->formatCountryCalendarRecord($occurrence);
        $eventEnd = $occurrence['ends_at'];

        if ($record['all_day'] && $eventEnd) {
            $eventEnd = $eventEnd->addDay()->startOfDay();
        }

        return [
            'id' => $record['id'],
            'title' => $record['title'],
            'start' => $occurrence['starts_at']->toIso8601String(),
            'end' => $eventEnd?->toIso8601String(),
            'allDay' => $record['all_day'],
            'backgroundColor' => $record['color'],
            'borderColor' => $record['color'],
            'textColor' => '#ffffff',
            'editable' => false,
            'startEditable' => false,
            'durationEditable' => false,
            'extendedProps' => $record,
        ];
    }

    private function formatRendezVousRecord(RendezVous $rendezVous): array
    {
        $etablissement = $rendezVous->etablissement;

        return [
            'id' => (string) $rendezVous->id,
            'source_type' => 'rendezvous',
            'etablissement_id' => $rendezVous->etablissement_id,
            'etablissement_name' => $etablissement?->name,
            'etablissement_city' => $etablissement?->ville,
            'etablissement_email' => $etablissement?->email_contact,
            'etablissement_phone' => $etablissement?->phone,
            'country_id' => $etablissement?->country_id,
            'title' => $rendezVous->title,
            'contact_name' => $rendezVous->contact_name,
            'contact_email' => $rendezVous->contact_email,
            'contact_phone' => $rendezVous->contact_phone,
            'starts_at' => $rendezVous->starts_at?->format('Y-m-d\TH:i'),
            'ends_at' => $rendezVous->ends_at?->format('Y-m-d\TH:i'),
            'all_day' => (bool) $rendezVous->all_day,
            'status' => $rendezVous->status,
            'status_label' => $this->statusOptions()[$rendezVous->status] ?? $rendezVous->status,
            'meeting_type' => $rendezVous->meeting_type,
            'meeting_type_label' => $this->meetingTypeOptions()[$rendezVous->meeting_type] ?? null,
            'location' => $rendezVous->location,
            'notes' => $rendezVous->notes,
            'color' => $rendezVous->resolved_color,
            'created_by' => $rendezVous->created_by,
            'created_at' => $rendezVous->created_at?->format('d/m/Y H:i'),
            'updated_at' => $rendezVous->updated_at?->format('d/m/Y H:i'),
        ];
    }

    private function formatCountryCalendarRecord(array $occurrence): array
    {
        return [
            'id' => $occurrence['id'],
            'source_type' => 'country_calendar_event',
            'country_calendar_event_id' => $occurrence['country_calendar_event_id'],
            'country_id' => $occurrence['country_id'],
            'country_name' => $occurrence['country_name'],
            'etablissement_id' => null,
            'etablissement_name' => $occurrence['country_name'],
            'etablissement_city' => 'Calendrier pays',
            'etablissement_email' => null,
            'etablissement_phone' => null,
            'title' => $occurrence['title'],
            'contact_name' => null,
            'contact_email' => null,
            'contact_phone' => null,
            'starts_at' => $occurrence['starts_at']->format('Y-m-d\TH:i'),
            'ends_at' => $occurrence['ends_at']->format('Y-m-d\TH:i'),
            'all_day' => (bool) $occurrence['all_day'],
            'status' => $occurrence['event_type'],
            'status_label' => $occurrence['event_type_label'],
            'meeting_type' => 'country_event',
            'meeting_type_label' => 'Evenement pays',
            'location' => $occurrence['country_name'],
            'notes' => $occurrence['description'] ?: 'Evenement pays synchronise automatiquement.',
            'color' => $occurrence['color'],
            'created_by' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
    }

    private function resolveCountryEvents(Request $request): Collection
    {
        if (!$request->filled('etablissement_id')) {
            return collect();
        }

        $etablissement = Etablissement::query()
            ->select(['id', 'country_id'])
            ->find((int) $request->input('etablissement_id'));

        if (!$etablissement?->country_id) {
            return collect();
        }

        $rangeStart = $request->filled('start')
            ? CarbonImmutable::parse($request->input('start'))->startOfDay()
            : now()->startOfMonth()->toImmutable();
        $rangeEnd = $request->filled('end')
            ? CarbonImmutable::parse($request->input('end'))->endOfDay()
            : now()->endOfMonth()->toImmutable();

        return $this->countryCalendarEventResolver->resolveForCountry(
            (int) $etablissement->country_id,
            $rangeStart,
            $rangeEnd
        );
    }

    private function statusOptions(): array
    {
        return [
            'planned' => 'Planifie',
            'confirmed' => 'Confirme',
            'completed' => 'Termine',
            'cancelled' => 'Annule',
            'rescheduled' => 'Reporte',
        ];
    }

    private function meetingTypeOptions(): array
    {
        return [
            'sur_place' => 'Sur place',
            'visio' => 'Visio',
            'appel' => 'Appel',
            'demo' => 'Demo',
            'suivi' => 'Suivi',
        ];
    }
}
