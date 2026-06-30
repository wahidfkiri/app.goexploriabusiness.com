<?php

namespace Vendor\Ecommerce\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BillingSetting;
use App\Models\Etablissement;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Tax;
use App\Models\BillingDiscount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    /**
     * Affiche la liste des factures
     */
    public function index(Request $request)
    {
        if ($request->ajax() || $request->boolean('ajax')) {
            $query = Invoice::with(['client']);

            $this->applyIndexFilters($query, $request, 'invoice_date');

            $invoices = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => collect($invoices->items())->map(fn (Invoice $invoice) => $this->invoicePayload($invoice))->values(),
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
                'prev_page_url' => $invoices->previousPageUrl(),
                'next_page_url' => $invoices->nextPageUrl(),
            ]);
        }

        return view('ecommerce::invoices.index', [
            'clients' => $this->clientEtablissements(),
            'stats' => $this->getStatistics(),
        ]);
    }

    /**
     * Affiche le formulaire de création de facture
     */
    public function create(Request $request)
    {
        return view('ecommerce::invoices.form', $this->formData($request) + [
            'document' => null,
            'documentType' => 'invoice',
            'mode' => 'create',
            'storeUrl' => route('invoices.store'),
            'backUrl' => route('invoices.index'),
        ]);
    }

    /**
     * Crée une nouvelle facture
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateDocument($request, 'invoice');
            $invoice = DB::transaction(fn () => $this->persistInvoice($request, $validated));

            if ($invoice->status === 'envoyee') {
                $this->sendInvoiceEmail($invoice->fresh(['client', 'lines']));
            }

            return response()->json([
                'success' => true,
                'message' => 'Facture créée avec succès.',
                'redirect_url' => route('invoices.show', $invoice),
                'data' => ['id' => $invoice->id, 'number' => $invoice->invoice_number],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation impossible.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Erreur pendant la création de la facture.'], 500);
        }
    }

    /**
     * Affiche les détails d'une facture
     */
    public function show(Request $request, $id)
    {
        $invoice = Invoice::with(['client', 'quote', 'lines.product', 'payments'])
            ->findOrFail($id);

        $billingSettings = $this->getBillingSettings();
        $relatedInvoices = Invoice::where('client_id', $invoice->client_id)
            ->where('id', '!=', $invoice->id)
            ->latest('invoice_date')
            ->limit(5)
            ->get();

        return view('ecommerce::invoices.show', compact('invoice', 'billingSettings', 'relatedInvoices'));
    }

    /**
     * Affiche le formulaire d'édition d'une facture
     */
    public function edit(Request $request, $id)
    {
        $invoice = Invoice::with(['lines', 'client'])->findOrFail($id);

        if (in_array($invoice->status, ['payee', 'annulee'], true)) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Cette facture ne peut pas être modifiée.');
        }

        return view('ecommerce::invoices.form', $this->formData($request) + [
            'document' => $invoice,
            'documentType' => 'invoice',
            'mode' => 'edit',
            'storeUrl' => route('invoices.update', $invoice),
            'backUrl' => route('invoices.show', $invoice),
        ]);
    }

    /**
     * Met à jour une facture
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);

            if (in_array($invoice->status, ['payee', 'annulee'], true)) {
                return response()->json(['success' => false, 'message' => 'Cette facture ne peut pas être modifiée.'], 409);
            }

            $validated = $this->validateDocument($request, 'invoice');
            $invoice = DB::transaction(fn () => $this->persistInvoice($request, $validated, $invoice));

            return response()->json([
                'success' => true,
                'message' => 'Facture mise à jour avec succès.',
                'redirect_url' => route('invoices.show', $invoice),
                'data' => ['id' => $invoice->id, 'number' => $invoice->invoice_number],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation impossible.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Erreur pendant la mise à jour de la facture.'], 500);
        }
    }

    /**
     * Supprime une facture
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status === 'payee' || $invoice->payments()->exists()) {
            return response()->json(['success' => false, 'message' => 'Impossible de supprimer une facture payée ou liée à des paiements.'], 409);
        }

        $invoice->delete();

        return response()->json(['success' => true, 'message' => 'Facture supprimée avec succès.']);
    }

    /**
     * Envoie une facture par email
     */
    public function sendEmail(Request $request, $id): JsonResponse
    {
        $invoice = Invoice::with(['client', 'lines'])->findOrFail($id);

        $this->sendInvoiceEmail($invoice);
        $invoice->update(['status' => 'envoyee']);

        return response()->json(['success' => true, 'message' => 'Facture envoyée par email avec succès.']);
    }

    /**
     * Télécharge une facture en PDF
     */
    public function downloadPdf(Request $request, $id): Response
    {
        $invoice = Invoice::with(['client', 'lines'])->findOrFail($id);
        $invoice = $this->applyGlobalTaxesAndDiscounts($invoice);

        $billingSettings = $this->getBillingSettings();
        $pdf = $this->generatePdfBinary('ecommerce::invoices.pdf', compact('invoice', 'billingSettings'), 'Facture ' . $invoice->invoice_number);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="facture-' . $invoice->invoice_number . '.pdf"',
        ]);
    }

    /**
     * Marque une facture comme payée
     */
    public function markAsPaid(Request $request, $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->update([
            'status' => 'payee',
            'payment_date' => now(),
            'paid_amount' => $invoice->total,
            'remaining_amount' => 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Facture marquée comme payée.']);
    }

    /**
     * Statistiques des factures
     */
    public function statistics(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->getStatistics()]);
    }

    /**
     * Persiste une facture en base de données
     */
    protected function persistInvoice(Request $request, array $validated, ?Invoice $invoice = null): Invoice
    {
        $client = Etablissement::findOrFail($validated['client_id']);
        $totals = $this->calculateTotals($validated['lines'], $validated);
        $metadata = array_merge($invoice?->metadata ?? [], $this->documentMetadata($request, $invoice));

        $invoiceData = [
            'client_id' => $client->id,
            'project_id' => null,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $totals['subtotal'],
            'shipping_fees' => $totals['shipping_fees'],
            'administration_fees' => $totals['administration_fees'],
            'discount_percentage' => $totals['discount_percentage'],
            'discount_amount' => $totals['discount_amount'],
            'tax_total' => $totals['tax_total'],
            'total' => $totals['total'],
            'paid_amount' => $invoice?->paid_amount ?? 0,
            'remaining_amount' => max(0, $totals['total'] - (float) ($invoice?->paid_amount ?? 0)),
            'taxes_breakdown' => array_values($totals['taxes_breakdown']),
            'status' => $validated['status'],
            'client_name' => $client->name,
            'client_address' => $client->adresse,
            'client_zipcode' => $client->zip_code,
            'client_city' => $client->villeRelation?->name ?? $client->ville,
            'client_country' => $client->country?->name ?? 'Canada',
            'client_vat_number' => null,
            'notes' => $validated['notes'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'footer' => $validated['footer'] ?? null,
            'metadata' => $metadata,
        ];

        $invoice ? $invoice->update($invoiceData) : $invoice = Invoice::create($invoiceData);
        $invoice->lines()->delete();

        foreach ($totals['lines'] as $index => $line) {
            InvoiceLine::create($line + ['invoice_id' => $invoice->id, 'line_number' => $index + 1]);
        }

        return $invoice->fresh(['lines', 'client']);
    }

    /**
     * Valide les données d'une facture ou d'un devis
     */
    protected function validateDocument(Request $request, string $type): array
    {
        return $request->validate([
            'client_id' => ['required', 'integer', 'exists:etablissements,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:500'],
            'lines.*.detailed_description' => ['nullable', 'string'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'lines.*.type' => ['nullable', 'in:produit,service,prestation,remise,frais'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_fees' => ['nullable', 'numeric', 'min:0'],
            'administration_fees' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'footer' => ['nullable', 'string'],
            'status' => ['required', $type === 'quote'
                ? 'in:brouillon,envoye,en_attente,accepte,refuse,annule'
                : 'in:brouillon,envoyee,en_attente,annulee'
            ],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Calcule les totaux d'une facture
     */
    protected function calculateTotals(array $lines, array $payload): array
    {
        $result = ['subtotal' => 0, 'tax_total' => 0, 'taxes_breakdown' => [], 'lines' => []];
        $taxesEnabled = $this->taxesAreEnabled($this->getBillingSettings());

        foreach ($lines as $line) {
            $quantity = (float) $line['quantity'];
            $unitPrice = (float) $line['unit_price'];
            $taxRate = $taxesEnabled ? (float) ($line['tax_rate'] ?? 0) : 0.0;
            $subtotal = round($quantity * $unitPrice, 2);
            $taxAmount = round($subtotal * ($taxRate / 100), 2);

            $result['subtotal'] += $subtotal;
            $result['tax_total'] += $taxAmount;
            $key = (string) $taxRate;
            $result['taxes_breakdown'][$key] ??= ['rate' => $taxRate, 'base' => 0, 'amount' => 0];
            $result['taxes_breakdown'][$key]['base'] += $subtotal;
            $result['taxes_breakdown'][$key]['amount'] += $taxAmount;

            $result['lines'][] = [
                'product_id' => $line['product_id'] ?? null,
                'description' => $line['description'],
                'detailed_description' => $line['detailed_description'] ?? null,
                'type' => $line['type'] ?? 'prestation',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
            ];
        }

        $result['discount_percentage'] = (float) ($payload['discount_percentage'] ?? 0);
        $result['shipping_fees'] = (float) ($payload['shipping_fees'] ?? 0);
        $result['administration_fees'] = (float) ($payload['administration_fees'] ?? 0);
        $result['discount_amount'] = round($result['subtotal'] * ($result['discount_percentage'] / 100), 2);
        $result['total'] = round($result['subtotal'] - $result['discount_amount'] + $result['shipping_fees'] + $result['administration_fees'] + $result['tax_total'], 2);

        return $result;
    }

    /**
     * Gère les métadonnées du document (logo)
     */
    protected function documentMetadata(Request $request, ?Invoice $invoice = null): array
    {
        $metadata = $invoice?->metadata ?? [];

        if ($request->boolean('remove_logo')) {
            unset($metadata['logo_url']);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('billing/logos', 'public');
            $metadata['logo_url'] = url(Storage::disk('public')->url($path));
        }

        return $metadata;
    }

    /**
     * Données pour le formulaire
     */
    protected function formData(Request $request): array
    {
        $settings = $this->getBillingSettings();

        return [
            'clients' => $this->clientEtablissements(),
            'products' => Product::where('is_available_for_sale', true)->orderBy('name')->get(),
            'taxes' => Tax::where('is_active', true)->orderBy('rate')->get(),
            'billingSettings' => $settings,
            'defaultDueDays' => $settings->payment_deadline_days ?: 30,
        ];
    }

    /**
     * Récupère les établissements clients
     */
    protected function clientEtablissements()
    {
        return Etablissement::query()
            ->orderBy('name')
            ->get(['id', 'name', 'lname', 'email_contact', 'phone', 'ville']);
    }

    /**
     * Récupère les paramètres de facturation globaux
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
     * Applique les filtres sur la liste des factures
     */
    protected function applyIndexFilters($query, Request $request, string $defaultSort): void
    {
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($clientQuery) => $clientQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('lname', 'like', "%{$search}%")
                        ->orWhere('email_contact', 'like', "%{$search}%")
                        ->orWhere('ville', 'like', "%{$search}%"));
            });
        }

        foreach (['status', 'client_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('date_from')) $query->whereDate($defaultSort, '>=', $request->date('date_from'));
        if ($request->filled('date_to')) $query->whereDate($defaultSort, '<=', $request->date('date_to'));
        if ($request->filled('min_amount')) $query->where('total', '>=', $request->input('min_amount'));
        if ($request->filled('max_amount')) $query->where('total', '<=', $request->input('max_amount'));

        $sortBy = in_array($request->input('sort_by'), ['invoice_date', 'due_date', 'total', 'created_at'], true) ? $request->input('sort_by') : $defaultSort;
        $sortOrder = $request->input('sort_order') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Payload d'une facture pour l'API
     */
    protected function invoicePayload(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'client_name' => $invoice->client_name,
            'client_city' => $invoice->client_city,
            'invoice_date' => optional($invoice->invoice_date)->toDateString(),
            'due_date' => optional($invoice->due_date)->toDateString(),
            'created_at' => optional($invoice->created_at)->toDateTimeString(),
            'total' => (float) $invoice->total,
            'paid_amount' => (float) $invoice->paid_amount,
            'remaining_amount' => (float) $invoice->remaining_amount,
            'status' => $invoice->status,
            'status_label' => $invoice->status_label,
            'is_overdue' => $invoice->is_overdue,
        ];
    }

    /**
     * Statistiques des factures
     */
    protected function getStatistics(): array
    {
        return [
            'total' => Invoice::count(),
            'total_amount' => Invoice::where('status', '!=', 'annulee')->sum('total'),
            'paid_amount' => Invoice::where('status', 'payee')->sum('total'),
            'pending_amount' => Invoice::whereIn('status', ['envoyee', 'en_attente', 'partiellement_payee'])->sum(DB::raw('total - paid_amount')),
            'overdue_count' => Invoice::where('due_date', '<', now())->whereNotIn('status', ['payee', 'annulee'])->count(),
            'by_status' => Invoice::select('status', DB::raw('count(*) as total'), DB::raw('sum(total) as amount'))->groupBy('status')->get(),
            'by_month' => Invoice::where('status', '!=', 'annulee')->select(DB::raw('DATE_FORMAT(invoice_date, "%Y-%m") as month'), DB::raw('count(*) as total'), DB::raw('sum(total) as amount'))->groupBy('month')->orderByDesc('month')->limit(6)->get(),
        ];
    }

    /**
     * Envoie une facture par email
     */
    protected function sendInvoiceEmail(Invoice $invoice): void
    {
        if (!$invoice->client?->email_contact) {
            return;
        }

        $invoice = $this->applyGlobalTaxesAndDiscounts($invoice);

        $billingSettings = $this->getBillingSettings();
        $pdf = $this->generatePdfBinary('ecommerce::invoices.pdf', compact('invoice', 'billingSettings'), 'Facture ' . $invoice->invoice_number);

        Mail::send('ecommerce::emails.invoice', compact('invoice', 'billingSettings'), function ($message) use ($invoice, $pdf) {
            $message->to($invoice->client->email_contact, $invoice->client_name)
                ->subject('Facture ' . $invoice->invoice_number)
                ->attachData($pdf, 'facture-' . $invoice->invoice_number . '.pdf', ['mime' => 'application/pdf']);
        });
    }

    /**
     * Génère un PDF
     */
    protected function generatePdfBinary(string $view, array $data, string $title): string
    {
        $html = View::make($view, $data)->render();

        // Try Barryvdh DomPDF facade if available
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper('a4', 'portrait')
                ->setOptions($this->dompdfOptions())
                ->output();
        }

        if (!class_exists(\Dompdf\Dompdf::class)) {
            throw new \RuntimeException('Dompdf n est pas installe. Executez composer require dompdf/dompdf.');
        }

        $options = new \Dompdf\Options($this->dompdfOptions());
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Options pour Dompdf
     */
    protected function dompdfOptions(): array
    {
        $tempDir = storage_path('app/dompdf');
        $fontDir = storage_path('fonts');

        foreach ([$tempDir, $fontDir] as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
        }

        return [
            'defaultFont' => 'DejaVu Sans',
            'fontDir' => $fontDir,
            'fontCache' => $fontDir,
            'tempDir' => $tempDir,
            'chroot' => base_path(),
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'isPhpEnabled' => false,
            'dpi' => 120,
        ];
    }

    /**
     * Fallback PDF en cas d'erreur
     */
    protected function fallbackPdf(string $title, string $text): string
    {
        $html = $text;
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = str_ireplace(['<br>', '<br/>', '<br />'], "\n", $html);
        $text = strip_tags($html);
        $text = preg_replace('/[^\P{C}\n\t]+/u', '', $title . "\n\n" . $text);
        $lines = array_slice(array_filter(array_map('trim', preg_split('/\R/u', $text))), 0, 55);
        $content = "BT\n/F1 10 Tf\n50 790 Td\n";

        foreach ($lines as $index => $line) {
            $safe = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $line) ?: '');
            $content .= ($index ? "0 -14 Td\n" : '') . "({$safe}) Tj\n";
        }

        $content .= "ET";
        $objects = [
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n",
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj\n",
            "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n",
            "5 0 obj << /Length " . strlen($content) . " >> stream\n{$content}\nendstream endobj\n",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        return $pdf . "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    /**
     * Applique les taxes et remises globales sur un document
     */
    protected function applyGlobalTaxesAndDiscounts($document)
    {
        $settings = $this->getBillingSettings();
        $globalDiscounts = BillingDiscount::where('is_active', true)->get();

        if ($globalDiscounts->isNotEmpty()) {
            $globalDiscountAmount = 0;
            foreach ($globalDiscounts as $discount) {
                if ($discount->type === 'percentage') {
                    $globalDiscountAmount += $document->subtotal * ($discount->value / 100);
                } else {
                    $globalDiscountAmount += $discount->value;
                }
            }
            $document->discount_amount = round($globalDiscountAmount, 2);
        }

        if (!$this->taxesAreEnabled($settings)) {
            $document->tax_total = 0;
            $document->taxes_breakdown = [];
            $document->total = round(max(0, $document->subtotal - $document->discount_amount + $document->shipping_fees + $document->administration_fees), 2);

            return $document;
        }

        $globalTaxes = Tax::where('is_active', true)->get();
        if ($globalTaxes->isNotEmpty()) {
            $taxableAmount = max(0, $document->subtotal - $document->discount_amount + $document->shipping_fees + $document->administration_fees);
            $taxTotal = 0;
            $taxesBreakdown = [];
            foreach ($globalTaxes as $tax) {
                $taxAmount = round($taxableAmount * ($tax->rate / 100), 2);
                $taxTotal += $taxAmount;
                $taxesBreakdown[(string) $tax->rate] = [
                    'rate' => $tax->rate,
                    'base' => $taxableAmount,
                    'amount' => $taxAmount,
                    'name' => $tax->name,
                ];
            }

            $document->tax_total = $taxTotal;
            $document->taxes_breakdown = array_values($taxesBreakdown);
            $document->total = round($taxableAmount + $taxTotal, 2);
        } else if ($globalDiscounts->isNotEmpty()) {
            $document->total = round(max(0, $document->subtotal - $document->discount_amount + $document->shipping_fees + $document->administration_fees + $document->tax_total), 2);
        }

        return $document;
    }

    /**
     * Indique si les taxes doivent etre appliquees globalement.
     */
    protected function taxesAreEnabled(?BillingSetting $settings = null): bool
    {
        $settings ??= $this->getBillingSettings();

        return (bool) ($settings->taxes_enabled ?? true);
    }
}
