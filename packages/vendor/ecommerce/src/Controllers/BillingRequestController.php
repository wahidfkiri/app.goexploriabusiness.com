<?php

namespace Vendor\Ecommerce\Controllers;

use App\Models\BillingRequest;
use App\Models\BillingRequestItem;
use App\Models\BillingRequestService;
use App\Models\BillingSetting;
use App\Models\Etablissement;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BillingRequestController extends InvoiceController
{
    /**
     * Affiche la liste des services de facturation
     */
    public function servicesIndex(Request $request)
    {
        $etablissementId = auth()->user()->etablissement_id ?? Etablissement::query()->value('id');

        if (!$this->expectsJson($request)) {
            $services = BillingRequestService::with('tax')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get();

            return view('ecommerce::billing-requests.services', [
                'etablissementId' => $etablissementId,
                'taxes' => Tax::where('is_active', true)->orderBy('rate')->get(['id', 'name', 'code', 'rate', 'type']),
                'servicesPayload' => $services->map(fn (BillingRequestService $service) => $this->servicePayload($service))->values(),
            ]);
        }

        $services = BillingRequestService::with('tax')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services->map(fn (BillingRequestService $service) => $this->servicePayload($service))->values(),
            'taxes' => Tax::where('is_active', true)->orderBy('rate')->get(['id', 'name', 'code', 'rate', 'type']),
        ]);
    }

    /**
     * CrÃ©e un nouveau service de facturation
     */
    public function servicesStore(Request $request): JsonResponse
    {
        try {
            $data = $this->validateService($request);
            $data['image_url'] = $this->resolveServiceImage($request);

            $service = BillingRequestService::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Option de facturation crÃ©Ã©e avec succÃ¨s.',
                'data' => $this->servicePayload($service->fresh('tax')),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation impossible.', 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Met Ã  jour un service de facturation
     */
    public function servicesUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $service = BillingRequestService::findOrFail($id);
            $data = $this->validateService($request, $service);

            if ($request->boolean('remove_image')) {
                $this->deletePublicStorageUrl($service->image_url);
                $data['image_url'] = null;
            }

            if ($request->hasFile('image')) {
                $this->deletePublicStorageUrl($service->image_url);
                $data['image_url'] = $this->resolveServiceImage($request);
            } elseif ($request->filled('image_url')) {
                $data['image_url'] = $request->string('image_url')->toString();
            }

            $service->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Option de facturation mise Ã  jour.',
                'data' => $this->servicePayload($service->fresh('tax')),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation impossible.', 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Supprime un service de facturation
     */
    public function servicesDestroy(Request $request, int $id): JsonResponse
    {
        $service = BillingRequestService::findOrFail($id);
        $service->delete();

        return response()->json(['success' => true, 'message' => 'Option de facturation supprimÃ©e.']);
    }

    /**
     * Affiche la liste des demandes de facturation
     */
    public function requestsIndex(Request $request)
    {
        if (!$this->expectsJson($request)) {
            return view('ecommerce::billing-requests.index', [
                'stats' => $this->requestStats(),
            ]);
        }

        $query = BillingRequest::with(['items.service', 'quote', 'invoice'])
            ->withCount('items');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('request_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $requests = $query->latest('submitted_at')->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => collect($requests->items())->map(fn (BillingRequest $billingRequest) => $this->requestPayload($billingRequest))->values(),
            'current_page' => $requests->currentPage(),
            'last_page' => $requests->lastPage(),
            'total' => $requests->total(),
        ]);
    }

    /**
     * Affiche les dÃ©tails d'une demande de facturation
     */
    public function requestsShow(Request $request, int $id): JsonResponse
    {
        $billingRequest = BillingRequest::with(['items.service', 'quote', 'invoice'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->requestPayload($billingRequest, true),
        ]);
    }

    /**
     * Met Ã  jour le statut d'une demande de facturation
     */
    public function requestsUpdateStatus(Request $request, int $id): JsonResponse
    {
        $billingRequest = BillingRequest::findOrFail($id);
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'reviewed', 'quoted', 'invoiced', 'sent', 'closed', 'cancelled'])],
        ]);

        $billingRequest->update(['status' => $data['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis Ã  jour.',
            'data' => $this->requestPayload($billingRequest->fresh())
        ]);
    }

    /**
     * Supprime une demande de facturation
     * Uniquement possible si aucune facture ou devis n'est associÃ©
     */
    public function requestsDestroy(Request $request, int $id): JsonResponse
    {
        $billingRequest = BillingRequest::with(['items'])->findOrFail($id);

        // VÃ©rifier si la demande peut Ãªtre supprimÃ©e
        if ($billingRequest->invoice_id) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette demande car une facture lui est dÃ©jÃ  associÃ©e (#' . ($billingRequest->invoice?->invoice_number ?? $billingRequest->invoice_id) . ').',
                'code' => 'has_invoice'
            ], 422);
        }

        if ($billingRequest->quote_id) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette demande car un devis lui est dÃ©jÃ  associÃ© (#' . ($billingRequest->quote?->quote_number ?? $billingRequest->quote_id) . ').',
                'code' => 'has_quote'
            ], 422);
        }

        // VÃ©rifier que le statut n'est pas "fermÃ©" ou "envoyÃ©"
        if (in_array($billingRequest->status, ['closed', 'sent', 'invoiced'])) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une demande avec le statut "' . $billingRequest->status_label . '".',
                'code' => 'invalid_status'
            ], 422);
        }

        try {
            DB::transaction(function () use ($billingRequest) {
                // Supprimer les items associÃ©s
                $billingRequest->items()->delete();

                // Supprimer la demande
                $billingRequest->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Demande #' . $billingRequest->request_number . ' supprimÃ©e avec succÃ¨s.',
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime plusieurs demandes de facturation en masse
     */
    public function requestsBulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:billing_requests,id'],
        ]);

        $ids = $validated['ids'];
        $deleted = 0;
        $failed = [];
        $errors = [];

        $billingRequests = BillingRequest::with(['items', 'invoice', 'quote'])
            ->whereIn('id', $ids)
            ->get();

        foreach ($billingRequests as $billingRequest) {
            // VÃ©rifier les contraintes avant suppression
            if ($billingRequest->invoice_id) {
                $failed[] = $billingRequest->id;
                $errors[] = 'Demande #' . $billingRequest->request_number . ' a une facture associÃ©e.';
                continue;
            }

            if ($billingRequest->quote_id) {
                $failed[] = $billingRequest->id;
                $errors[] = 'Demande #' . $billingRequest->request_number . ' a un devis associÃ©.';
                continue;
            }

            if (in_array($billingRequest->status, ['closed', 'sent', 'invoiced'])) {
                $failed[] = $billingRequest->id;
                $errors[] = 'Demande #' . $billingRequest->request_number . ' a un statut non supprimable ("' . $billingRequest->status_label . '").';
                continue;
            }

            try {
                DB::transaction(function () use ($billingRequest) {
                    $billingRequest->items()->delete();
                    $billingRequest->delete();
                });
                $deleted++;
            } catch (\Exception $e) {
                $failed[] = $billingRequest->id;
                $errors[] = 'Demande #' . $billingRequest->request_number . ': ' . $e->getMessage();
            }
        }

        $message = $deleted . ' demande(s) supprimÃ©e(s) avec succÃ¨s.';
        if (!empty($failed)) {
            $message .= ' ' . count($failed) . ' demande(s) non supprimÃ©e(s).';
        }

        return response()->json([
            'success' => $deleted > 0,
            'message' => $message,
            'deleted' => $deleted,
            'failed' => $failed,
            'errors' => $errors,
            'total' => count($billingRequests),
        ]);
    }

    /**
     * VÃ©rifie si une demande peut Ãªtre supprimÃ©e
     */
    public function requestsCanDelete(Request $request, int $id): JsonResponse
    {
        $billingRequest = BillingRequest::findOrFail($id);

        $canDelete = true;
        $reason = null;

        if ($billingRequest->invoice_id) {
            $canDelete = false;
            $reason = 'Une facture est dÃ©jÃ  associÃ©e (#' . ($billingRequest->invoice?->invoice_number ?? $billingRequest->invoice_id) . ').';
        } elseif ($billingRequest->quote_id) {
            $canDelete = false;
            $reason = 'Un devis est dÃ©jÃ  associÃ© (#' . ($billingRequest->quote?->quote_number ?? $billingRequest->quote_id) . ').';
        } elseif (in_array($billingRequest->status, ['closed', 'sent', 'invoiced'])) {
            $canDelete = false;
            $reason = 'Le statut "' . $billingRequest->status_label . '" ne permet pas la suppression.';
        }

        return response()->json([
            'success' => true,
            'can_delete' => $canDelete,
            'reason' => $reason,
            'has_invoice' => (bool) $billingRequest->invoice_id,
            'has_quote' => (bool) $billingRequest->quote_id,
            'status' => $billingRequest->status,
        ]);
    }

    /**
     * Convertit une demande en devis
     */
    public function convertToQuote(Request $request, int $id)
    {
        $billingRequest = BillingRequest::with(['items.service', 'quote', 'invoice'])->findOrFail($id);

        if ($billingRequest->quote_id) {
            if ($request->boolean('download')) {
                $quote = Quote::with(['lines'])->findOrFail($billingRequest->quote_id);
                $billingSettings = $this->getBillingSettings();
                $pdf = $this->generatePdfBinary('ecommerce::quotes.pdf', compact('quote', 'billingSettings'), 'Devis ' . $quote->quote_number);

                return response($pdf, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="devis-' . $quote->quote_number . '.pdf"',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Un devis existe dÃ©jÃ  pour cette demande.',
                'redirect_url' => route('quotes.show', $billingRequest->quote_id),
            ]);
        }

        $quote = DB::transaction(fn () => $this->createQuoteFromRequest($billingRequest));

        if ($request->boolean('download')) {
            $quote = $quote->fresh(['lines']);
            $billingSettings = $this->getBillingSettings();
            $pdf = $this->generatePdfBinary('ecommerce::quotes.pdf', compact('quote', 'billingSettings'), 'Devis ' . $quote->quote_number);

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="devis-' . $quote->quote_number . '.pdf"',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Demande transformÃ©e en devis.',
            'redirect_url' => route('quotes.show', $quote),
            'data' => ['id' => $quote->id, 'number' => $quote->quote_number],
        ]);
    }

    /**
     * Convertit une demande en facture
     */
    public function convertToInvoice(Request $request, int $id)
    {
        $billingRequest = BillingRequest::with(['items.service', 'quote', 'invoice'])->findOrFail($id);

        if ($billingRequest->invoice_id) {
            if ($request->boolean('download')) {
                $invoice = Invoice::with(['lines'])->findOrFail($billingRequest->invoice_id);
                $billingSettings = $this->getBillingSettings();
                $pdf = $this->generatePdfBinary('ecommerce::invoices.pdf', compact('invoice', 'billingSettings'), 'Facture ' . $invoice->invoice_number);

                return response($pdf, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="facture-' . $invoice->invoice_number . '.pdf"',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Une facture existe dÃ©jÃ  pour cette demande.',
                'redirect_url' => route('invoices.show', $billingRequest->invoice_id),
            ]);
        }

        $invoice = DB::transaction(fn () => $this->createInvoiceFromRequest($billingRequest));

        if ($request->boolean('download')) {
            $invoice = $invoice->fresh(['lines']);
            $billingSettings = $this->getBillingSettings();
            $pdf = $this->generatePdfBinary('ecommerce::invoices.pdf', compact('invoice', 'billingSettings'), 'Facture ' . $invoice->invoice_number);

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="facture-' . $invoice->invoice_number . '.pdf"',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Demande transformÃ©e en facture.',
            'redirect_url' => route('invoices.show', $invoice),
            'data' => ['id' => $invoice->id, 'number' => $invoice->invoice_number],
        ]);
    }

    /**
     * Envoie la facture au demandeur
     */
    public function sendInvoice(Request $request, int $id): JsonResponse
    {
        $billingRequest = BillingRequest::with(['items.service', 'quote', 'invoice'])->findOrFail($id);

        // CrÃ©er la facture si elle n'existe pas encore
        $invoice = $billingRequest->invoice ?: DB::transaction(fn () => $this->createInvoiceFromRequest($billingRequest));

        $billingSettings = $this->getBillingSettings();

        $this->sendInvoiceToRequester($invoice->fresh(['lines']), $billingRequest, $billingSettings);
        $invoice->update(['status' => 'envoyee']);
        $billingRequest->update(['status' => 'sent', 'processed_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Facture gÃ©nÃ©rÃ©e et envoyÃ©e au demandeur.',
            'redirect_url' => route('invoices.show', $invoice),
        ]);
    }

    /**
     * Formulaire public de demande de facturation
     */
    public function publicForm(int $etablissementId)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);
        $settings = $this->getBillingSettings();
        $services = BillingRequestService::with('tax')
            ->active()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('ecommerce::billing-requests.public-form', [
            'etablissement' => $etablissement,
            'settings' => $settings,
            'services' => $services,
            'servicesPayload' => $services->map(fn (BillingRequestService $service) => $this->servicePayload($service))->values(),
            'pricingSettings' => [
                'global_tax_rate' => $this->globalTaxRate($settings),
                'discount' => $this->globalDiscount($settings),
            ],
        ]);
    }

    /**
     * RÃ©cupÃ¨re les options publiques pour une demande de facturation
     */
    public function publicOptions(int $etablissementId): JsonResponse
    {
        $etablissement = Etablissement::findOrFail($etablissementId);
        $settings = $this->getBillingSettings();

        $services = BillingRequestService::with('tax')
            ->active()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return response()->json([
            'success' => true,
            'etablissement' => [
                'id' => $etablissement->id,
                'name' => $etablissement->name,
            ],
            'settings' => [
                'currency' => $settings->currency ?: 'CAD',
                'locale' => $settings->locale ?: 'fr_CA',
                'default_tax_ids' => $settings->default_tax_ids ?: [],
                'global_tax_rate' => $this->globalTaxRate($settings),
                'discount' => $this->globalDiscount($settings),
            ],
            'services' => $services->map(fn (BillingRequestService $service) => $this->servicePayload($service))->values(),
            'taxes' => Tax::where('is_active', true)->orderBy('rate')->get(['id', 'name', 'code', 'rate', 'type']),
        ]);
    }

    /**
     * Soumission publique d'une demande de facturation
     */
    public function publicSubmit(Request $request, int $etablissementId): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $data = $request->validate([
                'name' => ['required', 'string', 'max:191'],
                'email' => ['required', 'email', 'max:191'],
                'phone' => ['nullable', 'string', 'max:50'],
                'company' => ['nullable', 'string', 'max:191'],
                'address' => ['nullable', 'string', 'max:255'],
                'zipcode' => ['nullable', 'string', 'max:30'],
                'city' => ['nullable', 'string', 'max:120'],
                'country' => ['nullable', 'string', 'max:120'],
                'message' => ['nullable', 'string'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.service_id' => ['required', 'integer', 'exists:billing_request_services,id'],
                'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            ]);

            $billingRequest = DB::transaction(fn () => $this->createBillingRequest($etablissement, $data, $request));

            return response()->json([
                'success' => true,
                'message' => 'Votre demande de facturation a Ã©tÃ© envoyÃ©e.',
                'data' => $this->requestPayload($billingRequest->fresh(['items.service']), true),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation impossible.', 'errors' => $e->errors()], 422);
        }
    }

    /**
     * CrÃ©e une demande de facturation
     */
    protected function createBillingRequest(Etablissement $etablissement, array $data, Request $request): BillingRequest
    {
        $serviceIds = collect($data['items'])->pluck('service_id')->map(fn ($id) => (int) $id)->all();
        $services = BillingRequestService::whereIn('id', $serviceIds)
            ->active()
            ->get()
            ->keyBy('id');

        if ($services->count() !== count(array_unique($serviceIds))) {
            throw ValidationException::withMessages(['items' => 'Une ou plusieurs options ne sont pas disponibles.']);
        }

        $settings = $this->getBillingSettings();
        $totals = $this->calculateRequestTotals($data['items'], $services, $settings);

        $billingRequest = BillingRequest::create([
            'status' => 'new',
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'address' => $data['address'] ?? null,
            'zipcode' => $data['zipcode'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? 'Canada',
            'message' => $data['message'] ?? null,
            'subtotal' => $totals['subtotal'],
            'discount_type' => $totals['discount_type'],
            'discount_value' => $totals['discount_value'],
            'discount_amount' => $totals['discount_amount'],
            'tax_total' => $totals['tax_total'],
            'total' => $totals['total'],
            'taxes_breakdown' => array_values($totals['taxes_breakdown']),
            'metadata' => [
                'source_url' => $request->headers->get('referer'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'etablissement_id' => $etablissement->id,
                'etablissement_name' => $etablissement->name,
            ],
        ]);

        foreach ($totals['items'] as $lineNumber => $item) {
            BillingRequestItem::create($item + [
                'billing_request_id' => $billingRequest->id,
                'line_number' => $lineNumber + 1,
            ]);
        }

        return $billingRequest;
    }

    /**
     * CrÃ©e une facture Ã  partir d'une demande
     */
    protected function createInvoiceFromRequest(BillingRequest $billingRequest): Invoice
    {
        $settings = $this->getBillingSettings();
        $clientId = $this->resolveBillingRequestClientId($billingRequest);

        $invoice = Invoice::create([
            'client_id' => $clientId,
            'client_name' => $billingRequest->company ?: $billingRequest->name,
            'client_email' => $billingRequest->email,
            'client_phone' => $billingRequest->phone,
            'client_address' => $billingRequest->address,
            'client_zipcode' => $billingRequest->zipcode,
            'client_city' => $billingRequest->city,
            'client_country' => $billingRequest->country ?: 'Canada',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays((int) ($settings->payment_deadline_days ?: 30))->toDateString(),
            'subtotal' => $billingRequest->subtotal,
            'shipping_fees' => 0,
            'administration_fees' => 0,
            'discount_percentage' => $billingRequest->discount_type === 'percentage' ? $billingRequest->discount_value : 0,
            'discount_amount' => $billingRequest->discount_amount,
            'tax_total' => $billingRequest->tax_total,
            'total' => $billingRequest->total,
            'paid_amount' => 0,
            'remaining_amount' => $billingRequest->total,
            'taxes_breakdown' => $billingRequest->taxes_breakdown,
            'status' => 'brouillon',
            'client_vat_number' => null,
            'notes' => $billingRequest->message,
            'internal_notes' => 'CrÃ©Ã©e depuis la demande ' . $billingRequest->request_number,
            'footer' => $settings->invoice_footer_note,
            'metadata' => [
                'billing_request_id' => $billingRequest->id,
                'billing_request_number' => $billingRequest->request_number,
                'requester_email' => $billingRequest->email,
                'requester_phone' => $billingRequest->phone,
                'requester_name' => $billingRequest->name,
                'requester_company' => $billingRequest->company,
                'requester_address' => $billingRequest->address,
                'requester_city' => $billingRequest->city,
                'requester_country' => $billingRequest->country,
                'etablissement_name' => data_get($billingRequest->metadata, 'etablissement_name'),
            ],
        ]);

        foreach ($billingRequest->items as $index => $item) {
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'line_number' => $index + 1,
                'description' => $item->title,
                'detailed_description' => $item->description,
                'type' => 'prestation',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'subtotal' => $item->subtotal,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'total' => $item->total,
                'metadata' => ['billing_request_item_id' => $item->id],
            ]);
        }

        $billingRequest->update([
            'invoice_id' => $invoice->id,
            'status' => 'invoiced',
            'processed_at' => now(),
        ]);

        return $invoice->fresh(['lines']);
    }

    /**
     * CrÃ©e un devis Ã  partir d'une demande
     */
    protected function createQuoteFromRequest(BillingRequest $billingRequest): Quote
    {
        $settings = $this->getBillingSettings();
        $clientId = $this->resolveBillingRequestClientId($billingRequest);

        $quote = Quote::create([
            'client_id' => $clientId,
            'client_name' => $billingRequest->company ?: $billingRequest->name,
            'client_email' => $billingRequest->email,
            'client_phone' => $billingRequest->phone,
            'client_address' => $billingRequest->address,
            'client_zipcode' => $billingRequest->zipcode,
            'client_city' => $billingRequest->city,
            'client_country' => $billingRequest->country ?: 'Canada',
            'quote_date' => now()->toDateString(),
            'valid_until' => now()->addDays((int) ($settings->quote_validity_days ?: 30))->toDateString(),
            'subtotal' => $billingRequest->subtotal,
            'shipping_fees' => 0,
            'administration_fees' => 0,
            'discount_percentage' => $billingRequest->discount_type === 'percentage' ? $billingRequest->discount_value : 0,
            'discount_amount' => $billingRequest->discount_amount,
            'tax_total' => $billingRequest->tax_total,
            'total' => $billingRequest->total,
            'taxes_breakdown' => $billingRequest->taxes_breakdown,
            'status' => 'brouillon',
            'notes' => $billingRequest->message,
            'conditions' => $settings->quote_footer_note ?: $settings->terms_and_conditions,
            'metadata' => [
                'billing_request_id' => $billingRequest->id,
                'billing_request_number' => $billingRequest->request_number,
                'requester_name' => $billingRequest->name,
                'requester_email' => $billingRequest->email,
                'requester_phone' => $billingRequest->phone,
                'requester_company' => $billingRequest->company,
                'requester_address' => $billingRequest->address,
                'requester_city' => $billingRequest->city,
                'requester_country' => $billingRequest->country,
                'etablissement_name' => data_get($billingRequest->metadata, 'etablissement_name'),
            ],
        ]);

        foreach ($billingRequest->items as $index => $item) {
            QuoteLine::create([
                'quote_id' => $quote->id,
                'line_number' => $index + 1,
                'description' => $item->title,
                'detailed_description' => $item->description,
                'type' => 'prestation',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'subtotal' => $item->subtotal,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'total' => $item->total,
                'metadata' => ['billing_request_item_id' => $item->id],
            ]);
        }

        $billingRequest->update([
            'quote_id' => $quote->id,
            'status' => 'quoted',
            'processed_at' => now(),
        ]);

        return $quote->fresh(['lines', 'client']);
    }

    /**
     * Calcule les totaux d'une demande
     */
    protected function calculateRequestTotals(array $items, $services, ?BillingSetting $settings = null): array
    {
        if (!$settings) {
            $settings = $this->getBillingSettings();
        }

        $globalTaxRate = $this->globalTaxRate($settings);
        $discount = $this->globalDiscount($settings);
        $result = [
            'subtotal' => 0,
            'discount_type' => $discount['type'],
            'discount_value' => $discount['value'],
            'discount_amount' => 0,
            'tax_total' => 0,
            'total' => 0,
            'taxes_breakdown' => [],
            'items' => [],
        ];

        foreach ($items as $item) {
            $service = $services->get((int) $item['service_id']);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = (float) $service->unit_price;
            $taxRate = $this->taxesAreEnabled($settings)
                ? ($globalTaxRate !== null ? $globalTaxRate : (float) $service->tax_rate)
                : 0.0;
            $subtotal = round($quantity * $unitPrice, 2);
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $total = round($subtotal + $taxAmount, 2);
            $key = (string) $taxRate;

            $result['subtotal'] += $subtotal;
            $result['tax_total'] += $taxAmount;
            $result['total'] += $total;
            $result['taxes_breakdown'][$key] ??= ['rate' => $taxRate, 'base' => 0, 'amount' => 0];
            $result['taxes_breakdown'][$key]['base'] += $subtotal;
            $result['taxes_breakdown'][$key]['amount'] += $taxAmount;
            $result['items'][] = [
                'billing_request_service_id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'metadata' => ['image_url' => $service->image_url, 'billing_unit' => $service->billing_unit],
            ];
        }

        $result['subtotal'] = round($result['subtotal'], 2);
        $result['discount_amount'] = $this->calculateDiscountAmount($result['subtotal'], $discount);
        $result['tax_total'] = round($result['tax_total'], 2);
        $result['total'] = round($result['subtotal'] - $result['discount_amount'] + $result['tax_total'], 2);

        return $result;
    }

    /**
     * RÃ©cupÃ¨re les paramÃ¨tres de facturation globaux
     */
    protected function getBillingSettings(): BillingSetting
    {
        $settings = BillingSetting::first();
        if (!$settings) {
            $settings = BillingSetting::create([]);
        }
        return $settings;
    }

    /**
     * Calcule le taux de taxe global
     */
    protected function globalTaxRate(?BillingSetting $settings): ?float
    {
        if (!$settings) {
            $settings = $this->getBillingSettings();
        }

        if (!$this->taxesAreEnabled($settings)) {
            return 0.0;
        }

        $taxIds = collect($settings->default_tax_ids ?? [])->filter()->map(fn ($id) => (int) $id)->all();
        if (!$taxIds) {
            return null;
        }

        return (float) Tax::whereIn('id', $taxIds)->where('is_active', true)->sum('rate');
    }

    /**
     * Rattache une demande publique a un client etablissement si possible.
     */
    protected function resolveBillingRequestClientId(BillingRequest $billingRequest): ?int
    {
        $metadataClientId = (int) data_get($billingRequest->metadata, 'client_id', 0);
        if ($metadataClientId && Etablissement::whereKey($metadataClientId)->exists()) {
            return $metadataClientId;
        }

        if ($billingRequest->email) {
            $clientId = Etablissement::where('email_contact', $billingRequest->email)->value('id');
            if ($clientId) {
                return (int) $clientId;
            }
        }

        $name = trim((string) ($billingRequest->company ?: $billingRequest->name));
        if ($name !== '') {
            $clientId = Etablissement::where('name', $name)->value('id');
            if ($clientId) {
                return (int) $clientId;
            }
        }

        $sourceEtablissementId = (int) data_get($billingRequest->metadata, 'etablissement_id', 0);
        if ($sourceEtablissementId && Etablissement::whereKey($sourceEtablissementId)->exists()) {
            return $sourceEtablissementId;
        }

        return Etablissement::query()->orderBy('id')->value('id');
    }

    /**
     * RÃ©cupÃ¨re la remise globale
     */
    protected function globalDiscount(?BillingSetting $settings): array
    {
        if (!$settings) {
            $settings = $this->getBillingSettings();
        }

        $discount = $settings->defaultDiscount;

        if ($discount && $discount->is_active) {
            return [
                'type' => $discount->type ?: 'percentage',
                'value' => (float) $discount->value,
            ];
        }

        $percentage = (float) ($settings->default_discount_percentage ?? 0);

        return [
            'type' => $percentage > 0 ? 'percentage' : null,
            'value' => $percentage,
        ];
    }

    /**
     * Calcule le montant de la remise
     */
    protected function calculateDiscountAmount(float $subtotal, array $discount): float
    {
        if (($discount['value'] ?? 0) <= 0) {
            return 0.0;
        }

        $amount = ($discount['type'] ?? 'percentage') === 'fixed'
            ? (float) $discount['value']
            : $subtotal * ((float) $discount['value'] / 100);

        return round(min($subtotal, max(0, $amount)), 2);
    }

    /**
     * Valide les donnÃ©es d'un service
     */
    protected function validateService(Request $request, ?BillingRequestService $service = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'remove_image' => ['nullable', 'boolean'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'billing_unit' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        if (!empty($data['tax_id']) && ($tax = Tax::find($data['tax_id']))) {
            $data['tax_rate'] = $tax->rate;
        }

        $data['tax_rate'] = (float) ($data['tax_rate'] ?? 0);
        $data['billing_unit'] = $data['billing_unit'] ?? 'forfait';
        $data['sort_order'] = (int) ($data['sort_order'] ?? ($service->sort_order ?? 0));
        $data['is_active'] = $request->boolean('is_active', $service?->is_active ?? true);
        $data['is_featured'] = $request->boolean('is_featured', $service?->is_featured ?? false);

        unset($data['image'], $data['remove_image']);

        return $data;
    }

    /**
     * RÃ©sout l'URL de l'image du service
     */
    protected function resolveServiceImage(Request $request): ?string
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('billing/request-services', 'public');
            return url(Storage::disk('public')->url($path));
        }

        return $request->filled('image_url') ? $request->string('image_url')->toString() : null;
    }

    /**
     * Supprime un fichier stockÃ©
     */
    protected function deletePublicStorageUrl(?string $url): void
    {
        if (!$url || !Str::contains($url, '/storage/')) {
            return;
        }

        $path = Str::after(parse_url($url, PHP_URL_PATH) ?: '', '/storage/');
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Envoie la facture par email
     */
    protected function sendInvoiceToRequester(Invoice $invoice, BillingRequest $billingRequest, ?BillingSetting $billingSettings = null): void
    {
        if (!$billingSettings) {
            $billingSettings = $this->getBillingSettings();
        }

        $pdf = $this->generatePdfBinary('ecommerce::invoices.pdf', compact('invoice', 'billingSettings'), 'Facture ' . $invoice->invoice_number);

        Mail::send('ecommerce::emails.invoice', compact('invoice', 'billingSettings'), function ($message) use ($invoice, $billingRequest, $pdf) {
            $message->to($billingRequest->email, $billingRequest->name)
                ->subject('Facture ' . $invoice->invoice_number)
                ->attachData($pdf, 'facture-' . $invoice->invoice_number . '.pdf', ['mime' => 'application/pdf']);
        });
    }

    /**
     * VÃ©rifie si la requÃªte attend du JSON
     */
    protected function expectsJson(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson() || $request->boolean('ajax');
    }

    /**
     * Statistiques des demandes
     */
    protected function requestStats(): array
    {
        return [
            'total' => BillingRequest::count(),
            'new' => BillingRequest::where('status', 'new')->count(),
            'quoted' => BillingRequest::where('status', 'quoted')->count(),
            'invoiced' => BillingRequest::whereIn('status', ['invoiced', 'sent'])->count(),
            'total_amount' => (float) BillingRequest::sum('total'),
        ];
    }

    /**
     * Payload d'un service
     */
    protected function servicePayload(BillingRequestService $service): array
    {
        return [
            'id' => $service->id,
            'title' => $service->title,
            'description' => $service->description,
            'image_url' => $service->image_url,
            'unit_price' => (float) $service->unit_price,
            'tax_id' => $service->tax_id,
            'tax_rate' => (float) $service->tax_rate,
            'billing_unit' => $service->billing_unit,
            'sort_order' => $service->sort_order,
            'is_active' => (bool) $service->is_active,
            'is_featured' => (bool) $service->is_featured,
            'tax' => $service->tax ? ['id' => $service->tax->id, 'name' => $service->tax->name, 'rate' => (float) $service->tax->rate] : null,
        ];
    }

    /**
     * Payload d'une demande
     */
    protected function requestPayload(BillingRequest $billingRequest, bool $full = false): array
    {
        $payload = [
            'id' => $billingRequest->id,
            'request_number' => $billingRequest->request_number,
            'status' => $billingRequest->status,
            'status_label' => $billingRequest->status_label,
            'name' => $billingRequest->name,
            'email' => $billingRequest->email,
            'phone' => $billingRequest->phone,
            'company' => $billingRequest->company,
            'subtotal' => (float) $billingRequest->subtotal,
            'discount_type' => $billingRequest->discount_type,
            'discount_value' => (float) $billingRequest->discount_value,
            'discount_amount' => (float) $billingRequest->discount_amount,
            'tax_total' => (float) $billingRequest->tax_total,
            'total' => (float) $billingRequest->total,
            'items_count' => (int) ($billingRequest->items_count ?? $billingRequest->items->count()),
            'submitted_at' => optional($billingRequest->submitted_at)->toDateTimeString(),
            'quote_id' => $billingRequest->quote_id,
            'invoice_id' => $billingRequest->invoice_id,
            'quote_number' => optional($billingRequest->quote)->quote_number,
            'invoice_number' => optional($billingRequest->invoice)->invoice_number,
        ];

        if ($full) {
            $payload += [
                'address' => $billingRequest->address,
                'zipcode' => $billingRequest->zipcode,
                'city' => $billingRequest->city,
                'country' => $billingRequest->country,
                'message' => $billingRequest->message,
                'taxes_breakdown' => $billingRequest->taxes_breakdown ?: [],
                'metadata' => $billingRequest->metadata ?: [],
                'items' => $billingRequest->items->map(fn (BillingRequestItem $item) => [
                    'id' => $item->id,
                    'service_id' => $item->billing_request_service_id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                    'tax_rate' => (float) $item->tax_rate,
                    'tax_amount' => (float) $item->tax_amount,
                    'total' => (float) $item->total,
                ])->values(),
            ];
        }

        return $payload;
    }
}
