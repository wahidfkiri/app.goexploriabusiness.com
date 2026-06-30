<?php

namespace Vendor\Cms\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vendor\Cms\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function index(Request $request, int $etablissementId): JsonResponse
    {
        $etablissement = Etablissement::findOrFail($etablissementId);

        $query = ContactMessage::query()
            ->forEtablissement((int) $etablissement->id)
            ->with(['page:id,title,slug', 'assignedUser:id,name,email']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderByDesc('created_at')->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => collect($messages->items())->map(fn (ContactMessage $message) => $this->payload($message))->values(),
            'stats' => $this->stats((int) $etablissement->id),
            'current_page' => $messages->currentPage(),
            'last_page' => $messages->lastPage(),
            'total' => $messages->total(),
        ]);
    }

    public function show(Request $request, int $etablissementId, int $id): JsonResponse
    {
        $message = ContactMessage::with(['page:id,title,slug', 'assignedUser:id,name,email'])
            ->forEtablissement($etablissementId)
            ->findOrFail($id);

        if ($message->status === 'new') {
            $message->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->payload($message->fresh(['page', 'assignedUser']), true),
            'stats' => $this->stats($etablissementId),
        ]);
    }

    public function update(Request $request, int $etablissementId, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:new,read,replied,archived,spam'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $message = ContactMessage::forEtablissement($etablissementId)->findOrFail($id);
        $updates = collect($validated)->filter(fn ($value) => $value !== null)->all();

        if (($updates['status'] ?? null) === 'read' && !$message->read_at) {
            $updates['read_at'] = now();
        }

        if (($updates['status'] ?? null) === 'replied') {
            $updates['replied_at'] = now();
        }

        if (($updates['status'] ?? null) === 'archived') {
            $updates['archived_at'] = now();
        }

        $message->update($updates);

        return response()->json([
            'success' => true,
            'message' => 'Message mis à jour avec succès.',
            'data' => $this->payload($message->fresh(['page', 'assignedUser'])),
            'stats' => $this->stats($etablissementId),
        ]);
    }

    public function bulkUpdate(Request $request, int $etablissementId): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'status' => ['required', 'in:new,read,replied,archived,spam'],
        ]);

        DB::connection('cms')->transaction(function () use ($validated, $etablissementId) {
            ContactMessage::forEtablissement($etablissementId)
                ->whereIn('id', $validated['ids'])
                ->update($this->statusUpdatePayload($validated['status']));
        });

        return response()->json([
            'success' => true,
            'message' => 'Messages mis à jour avec succès.',
            'stats' => $this->stats($etablissementId),
        ]);
    }

    public function destroy(Request $request, int $etablissementId, int $id): JsonResponse
    {
        ContactMessage::forEtablissement($etablissementId)->findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message supprimé avec succès.',
            'stats' => $this->stats($etablissementId),
        ]);
    }

    protected function statusUpdatePayload(string $status): array
    {
        $payload = ['status' => $status, 'updated_at' => now()];

        if ($status === 'read') {
            $payload['read_at'] = now();
        } elseif ($status === 'replied') {
            $payload['replied_at'] = now();
            $payload['read_at'] = now();
        } elseif ($status === 'archived') {
            $payload['archived_at'] = now();
        }

        return $payload;
    }

    protected function stats(int $etablissementId): array
    {
        return [
            'total' => ContactMessage::forEtablissement($etablissementId)->count(),
            'new' => ContactMessage::forEtablissement($etablissementId)->where('status', 'new')->count(),
            'read' => ContactMessage::forEtablissement($etablissementId)->where('status', 'read')->count(),
            'replied' => ContactMessage::forEtablissement($etablissementId)->where('status', 'replied')->count(),
            'archived' => ContactMessage::forEtablissement($etablissementId)->where('status', 'archived')->count(),
            'spam' => ContactMessage::forEtablissement($etablissementId)->where('status', 'spam')->count(),
        ];
    }

    protected function payload(ContactMessage $message, bool $full = false): array
    {
        $payload = [
            'id' => $message->id,
            'name' => $message->display_name,
            'email' => $message->email,
            'phone' => $message->phone,
            'company' => $message->company,
            'subject' => $message->subject ?: 'Sans objet',
            'preview' => str($message->message)->limit(140)->toString(),
            'status' => $message->status,
            'status_label' => $message->status_label,
            'priority' => $message->priority,
            'priority_label' => $message->priority_label,
            'source_url' => $message->source_url,
            'form_name' => $message->form_name,
            'page_title' => $message->page?->title,
            'created_at' => optional($message->created_at)->toDateTimeString(),
            'created_at_human' => optional($message->created_at)->diffForHumans(),
        ];

        if ($full) {
            $payload += [
                'message' => $message->message,
                'first_name' => $message->first_name,
                'last_name' => $message->last_name,
                'preferred_contact_method' => $message->preferred_contact_method,
                'consent' => $message->consent,
                'newsletter_opt_in' => $message->newsletter_opt_in,
                'referrer' => $message->referrer,
                'ip_address' => $message->ip_address,
                'user_agent' => $message->user_agent,
                'utm_source' => $message->utm_source,
                'utm_medium' => $message->utm_medium,
                'utm_campaign' => $message->utm_campaign,
                'metadata' => $message->metadata,
            ];
        }

        return $payload;
    }
}
