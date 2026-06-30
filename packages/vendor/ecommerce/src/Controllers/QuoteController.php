<?php

namespace Vendor\Ecommerce\Controllers;

use App\Models\Etablissement;
use App\Models\BillingSetting;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Quote;
use App\Models\QuoteLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;

class QuoteController extends InvoiceController
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->boolean('ajax')) {
            $query = Quote::with(['client', 'invoice']);

            if ($request->filled('search')) {
                $search = $request->string('search');
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('quote_number', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($clientQuery) => $clientQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('lname', 'like', "%{$search}%")
                            ->orWhere('email_contact', 'like', "%{$search}%")
                            ->orWhere('ville', 'like', "%{$search}%"));
                });
            }

            if ($request->filled('status')) $query->where('status', $request->input('status'));
            if ($request->filled('client_id')) $query->where('client_id', $request->input('client_id'));

            $quotes = $query->latest('quote_date')->paginate(15);

            return response()->json([
                'success' => true,
                'data' => collect($quotes->items())->map(fn (Quote $quote) => $this->quotePayload($quote))->values(),
                'current_page' => $quotes->currentPage(),
                'last_page' => $quotes->lastPage(),
                'per_page' => $quotes->perPage(),
                'total' => $quotes->total(),
                'prev_page_url' => $quotes->previousPageUrl(),
                'next_page_url' => $quotes->nextPageUrl(),
            ]);
        }

        return view('ecommerce::quotes.index', [
            'clients' => $this->clientEtablissements(),
            'stats' => $this->quoteStatistics(),
        ]);
    }

    public function create(Request $request)
    {
        return view('ecommerce::invoices.form', $this->formData($request) + [
            'document' => null,
            'documentType' => 'quote',
            'mode' => 'create',
            'storeUrl' => route('quotes.store'),
            'backUrl' => route('quotes.index'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateQuote($request);
            $quote = DB::transaction(fn () => $this->persistQuote($request, $validated));

            return response()->json([
                'success' => true,
                'message' => 'Devis créé avec succès.',
                'redirect_url' => route('quotes.show', $quote),
                'data' => ['id' => $quote->id, 'number' => $quote->quote_number],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation impossible.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Erreur pendant la création du devis.'], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $quote = Quote::with(['client', 'invoice', 'lines.product'])->findOrFail($id);

        $billingSettings = $this->getBillingSettings();

        return view('ecommerce::quotes.show', compact('quote', 'billingSettings'));
    }

    public function edit(Request $request, $id)
    {
        $quote = Quote::with(['lines', 'client'])->findOrFail($id);

        if (in_array($quote->status, ['converti_en_facture', 'annule'], true)) {
            return redirect()->route('quotes.show', $quote)->with('error', 'Ce devis ne peut pas être modifié.');
        }

        return view('ecommerce::invoices.form', $this->formData($request) + [
            'document' => $quote,
            'documentType' => 'quote',
            'mode' => 'edit',
            'storeUrl' => route('quotes.update', $quote),
            'backUrl' => route('quotes.show', $quote),
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $quote = Quote::findOrFail($id);

            if (in_array($quote->status, ['converti_en_facture', 'annule'], true)) {
                return response()->json(['success' => false, 'message' => 'Ce devis ne peut pas être modifié.'], 409);
            }

            $validated = $this->validateQuote($request);
            $quote = DB::transaction(fn () => $this->persistQuote($request, $validated, $quote));

            return response()->json([
                'success' => true,
                'message' => 'Devis mis à jour avec succès.',
                'redirect_url' => route('quotes.show', $quote),
                'data' => ['id' => $quote->id, 'number' => $quote->quote_number],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation impossible.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Erreur pendant la mise à jour du devis.'], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $quote = Quote::findOrFail($id);

        if ($quote->invoice()->exists()) {
            return response()->json(['success' => false, 'message' => 'Impossible de supprimer un devis déjà transformé en facture.'], 409);
        }

        $quote->delete();

        return response()->json(['success' => true, 'message' => 'Devis supprimé avec succès.']);
    }

    public function convertToInvoice(Request $request, $id): JsonResponse
    {
        $quote = Quote::with(['lines', 'client'])->findOrFail($id);

        if ($quote->invoice) {
            return response()->json([
                'success' => true,
                'message' => 'Ce devis est déjà transformé en facture.',
                'redirect_url' => route('invoices.show', $quote->invoice),
            ]);
        }

        $invoice = DB::transaction(function () use ($quote) {
            $invoice = Invoice::create([
                'client_id' => $quote->client_id,
                'project_id' => null,
                'quote_id' => $quote->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $quote->subtotal,
                'shipping_fees' => $quote->shipping_fees,
                'administration_fees' => $quote->administration_fees,
                'discount_percentage' => $quote->discount_percentage,
                'discount_amount' => $quote->discount_amount,
                'tax_total' => $quote->tax_total,
                'total' => $quote->total,
                'paid_amount' => 0,
                'remaining_amount' => $quote->total,
                'taxes_breakdown' => $quote->taxes_breakdown,
                'status' => 'brouillon',
                'client_name' => $quote->client?->name ?: '',
                'client_address' => $quote->client?->adresse,
                'client_zipcode' => $quote->client?->zip_code,
                'client_city' => $quote->client?->villeRelation?->name ?? $quote->client?->ville,
                'client_country' => $quote->client?->country?->name ?? 'Canada',
                'client_vat_number' => null,
                'notes' => $quote->notes,
                'footer' => $quote->conditions,
                'metadata' => array_merge($quote->metadata ?? [], ['converted_from_quote' => $quote->quote_number]),
            ]);

            foreach ($quote->lines as $line) {
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $line->product_id,
                    'product_variant_id' => $line->product_variant_id,
                    'line_number' => $line->line_number,
                    'description' => $line->description,
                    'detailed_description' => $line->detailed_description,
                    'type' => $line->type,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'discount_percentage' => $line->discount_percentage,
                    'discount_amount' => $line->discount_amount,
                    'subtotal' => $line->subtotal,
                    'tax_rate' => $line->tax_rate,
                    'tax_amount' => $line->tax_amount,
                    'tax_id' => $line->tax_id,
                    'total' => $line->total,
                    'metadata' => $line->metadata,
                ]);
            }

            $quote->update(['status' => 'converti_en_facture']);

            return $invoice;
        });

        return response()->json([
            'success' => true,
            'message' => 'Devis transformé en facture avec succès.',
            'redirect_url' => route('invoices.show', $invoice),
        ]);
    }

    public function downloadPdf(Request $request, $id): Response
    {
        $quote = Quote::with(['client', 'lines'])->findOrFail($id);
        $quote = $this->applyGlobalTaxesAndDiscounts($quote);

        $billingSettings = $this->getBillingSettings();
        $pdf = $this->generatePdfBinary('ecommerce::quotes.pdf', compact('quote', 'billingSettings'), 'Devis ' . $quote->quote_number);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="devis-' . $quote->quote_number . '.pdf"',
        ]);
    }

    protected function validateQuote(Request $request): array
    {
        $validated = $this->validateDocument($request, 'quote');
        $validated['status'] = $request->input('status', 'brouillon');
        if (!in_array($validated['status'], ['brouillon', 'envoye', 'en_attente', 'accepte', 'refuse', 'annule'], true)) {
            $validated['status'] = 'brouillon';
        }

        return $validated;
    }

    protected function persistQuote(Request $request, array $validated, ?Quote $quote = null): Quote
    {
        $client = Etablissement::findOrFail($validated['client_id']);
        $totals = $this->calculateTotals($validated['lines'], $validated);
        $metadata = array_merge($quote?->metadata ?? [], $this->quoteMetadata($request, $quote));

        $quoteData = [
            'client_id' => $client->id,
            'project_id' => null,
            'quote_date' => $validated['invoice_date'],
            'valid_until' => $validated['due_date'],
            'subtotal' => $totals['subtotal'],
            'shipping_fees' => $totals['shipping_fees'],
            'administration_fees' => $totals['administration_fees'],
            'discount_percentage' => $totals['discount_percentage'],
            'discount_amount' => $totals['discount_amount'],
            'tax_total' => $totals['tax_total'],
            'total' => $totals['total'],
            'taxes_breakdown' => array_values($totals['taxes_breakdown']),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'conditions' => $validated['footer'] ?? null,
            'metadata' => $metadata,
        ];

        $quote ? $quote->update($quoteData) : $quote = Quote::create($quoteData);
        $quote->lines()->delete();

        foreach ($totals['lines'] as $index => $line) {
            QuoteLine::create(collect($line)->except(['invoice_id'])->all() + ['quote_id' => $quote->id, 'line_number' => $index + 1]);
        }

        return $quote->fresh(['lines', 'client']);
    }

    protected function quoteMetadata(Request $request, ?Quote $quote = null): array
    {
        $metadata = $quote?->metadata ?? [];

        if ($request->boolean('remove_logo')) unset($metadata['logo_url']);
        if ($request->hasFile('logo')) {
            $path = \Illuminate\Support\Facades\Storage::disk('public')->putFile('billing/logos', $request->file('logo'));
            $metadata['logo_url'] = url(\Illuminate\Support\Facades\Storage::disk('public')->url($path));
        }

        return $metadata;
    }

    protected function quotePayload(Quote $quote): array
    {
        return [
            'id' => $quote->id,
            'quote_number' => $quote->quote_number,
            'client_name' => $quote->client?->name,
            'quote_date' => optional($quote->quote_date)->toDateString(),
            'valid_until' => optional($quote->valid_until)->toDateString(),
            'total' => (float) $quote->total,
            'status' => $quote->status,
            'status_label' => $quote->status_label,
            'invoice_id' => $quote->invoice?->id,
        ];
    }

    protected function quoteStatistics(): array
    {
        return [
            'total' => Quote::count(),
            'total_amount' => Quote::where('status', '!=', 'annule')->sum('total'),
            'accepted' => Quote::where('status', 'accepte')->count(),
            'converted' => Quote::where('status', 'converti_en_facture')->count(),
        ];
    }
}
