<?php

namespace Vendor\Ecommerce\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Etablissement;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\OnlineOrder;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Vendor\Ecommerce\Services\Payment\PaymentService;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $etablissementId = $this->getEtablissementId();

        $query = OnlineOrder::with(['customer', 'invoice', 'items'])
            ->where('etablissement_id', $etablissementId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('nom', 'like', '%' . $search . '%')
                            ->orWhere('prenom', 'like', '%' . $search . '%')
                            ->orWhere('entreprise_nom', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate((int) $request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'total' => $orders->total(),
        ]);
    }

    public function show(int $id)
    {
        $order = OnlineOrder::with([
            'customer',
            'invoice.lines',
            'items.product',
            'items.variant',
            'transactions',
            'payments',
        ])
            ->where('etablissement_id', $this->getEtablissementId())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer.type' => 'nullable|in:particulier,entreprise',
            'customer.prenom' => 'nullable|string|max:191',
            'customer.nom' => 'nullable|string|max:191',
            'customer.email' => 'nullable|email|max:191',
            'customer.telephone' => 'nullable|string|max:50',
            'customer.entreprise_nom' => 'nullable|string|max:191',
            'customer.adresse' => 'nullable|string|max:255',
            'customer.code_postal' => 'nullable|string|max:20',
            'customer.ville' => 'nullable|string|max:191',
            'customer.pays' => 'nullable|string|max:191',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'billing_address' => 'nullable|array',
            'shipping_address' => 'nullable|array',
            'create_invoice' => 'nullable|boolean',
        ]);

        $etablissementId = $this->getEtablissementId();

        DB::beginTransaction();
        try {
            $customer = $this->resolveCustomer($validated, $etablissementId);

            $itemsByProductId = collect($validated['items'])->keyBy('product_id');
            $productIds = $itemsByProductId->keys()->all();

            $products = Product::whereIn('id', $productIds)
                ->where('etablissement_id', $etablissementId)
                ->where('is_available_for_sale', true)
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($productIds)) {
                throw new \RuntimeException('Certains produits sont introuvables ou indisponibles à la vente.');
            }

            $subtotalHt = 0.0;
            $subtotalTtc = 0.0;
            $taxTotal = 0.0;
            $shippingAmount = (float) ($validated['shipping_amount'] ?? 0);
            $discountAmount = (float) ($validated['discount_amount'] ?? 0);

            $preparedItems = [];
            foreach ($validated['items'] as $item) {
                /** @var Product $product */
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['quantity'];
                $taxRate = (float) ($product->tax_rate ?? 0);
                $unitPriceTtc = (float) $product->price_ttc;
                $unitPriceHt = $unitPriceTtc / (1 + ($taxRate / 100));
                $lineSubtotalHt = round($unitPriceHt * $quantity, 2);
                $lineSubtotalTtc = round($unitPriceTtc * $quantity, 2);
                $lineTax = round($lineSubtotalTtc - $lineSubtotalHt, 2);

                $variantId = null;
                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::where('id', $item['variant_id'])
                        ->where('product_id', $product->id)
                        ->first();
                    if (!$variant) {
                        throw new \RuntimeException('Variante invalide pour le produit #' . $product->id);
                    }
                    $variantId = $variant->id;
                }

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variantId,
                    'product_name' => $product->name,
                    'product_reference' => $product->reference,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price_ht' => round($unitPriceHt, 2),
                    'unit_price_ttc' => round($unitPriceTtc, 2),
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'line_subtotal_ht' => $lineSubtotalHt,
                    'line_subtotal_ttc' => $lineSubtotalTtc,
                    'line_total' => $lineSubtotalTtc,
                    'metadata' => null,
                ];

                $subtotalHt += $lineSubtotalHt;
                $subtotalTtc += $lineSubtotalTtc;
                $taxTotal += $lineTax;
            }

            $grandTotal = round($subtotalTtc + $shippingAmount - $discountAmount, 2);
            if ($grandTotal < 0) {
                $grandTotal = 0;
            }

            $order = OnlineOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'etablissement_id' => $etablissementId,
                'customer_id' => $customer->id,
                'status' => 'pending_payment',
                'payment_status' => 'pending',
                'subtotal_ht' => round($subtotalHt, 2),
                'subtotal_ttc' => round($subtotalTtc, 2),
                'tax_total' => round($taxTotal, 2),
                'shipping_amount' => round($shippingAmount, 2),
                'discount_amount' => round($discountAmount, 2),
                'total' => $grandTotal,
                'currency' => strtoupper($validated['currency'] ?? 'EUR'),
                'billing_address' => $validated['billing_address'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'ordered_at' => now(),
            ]);

            foreach ($preparedItems as $preparedItem) {
                $order->items()->create($preparedItem);
            }

            if ($request->boolean('create_invoice', true)) {
                $invoice = $this->createInvoiceFromOrder($order);
                $order->update(['invoice_id' => $invoice->id]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commande marketplace créée avec succès.',
                'data' => $order->fresh(['items', 'customer', 'invoice']),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function checkout(Request $request, int $id)
    {
        $request->validate([
            'gateway' => 'required|string',
        ]);

        $order = OnlineOrder::with(['customer', 'invoice'])
            ->where('etablissement_id', $this->getEtablissementId())
            ->findOrFail($id);

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande est déjà payée.',
            ], 422);
        }

        if (!$order->invoice_id) {
            $invoice = $this->createInvoiceFromOrder($order);
            $order->update(['invoice_id' => $invoice->id]);
            $order->refresh();
        }

        $gateway = PaymentGateway::where('is_active', true)
            ->where('code', $request->gateway)
            ->firstOrFail();

        $paymentService = new PaymentService($gateway);

        $result = $paymentService->createPayment(
            (float) $order->total,
            $order->currency ?: 'EUR',
            [
                'order_id' => $order->id,
                'invoice_id' => $order->invoice_id,
                'description' => "Commande #{$order->order_number}",
                'email' => $order->customer?->email,
            ]
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erreur de paiement',
            ], 400);
        }

        PaymentTransaction::create([
            'etablissement_id' => $order->etablissement_id,
            'online_order_id' => $order->id,
            'invoice_id' => $order->invoice_id,
            'client_id' => $order->customer_id,
            'payment_gateway_id' => $gateway->id,
            'gateway_type' => $gateway->type,
            'amount' => $order->total,
            'currency' => $order->currency ?: 'EUR',
            'status' => 'pending',
            'gateway_transaction_id' => $result['order_id'] ?? $result['payment_intent_id'] ?? null,
            'gateway_response' => $result['data'] ?? null,
            'metadata' => ['source' => 'marketplace_order'],
        ]);

        $order->update([
            'payment_gateway_code' => $gateway->code,
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Paiement initialisé.',
            'redirect_url' => $result['approval_url'] ?? null,
            'client_secret' => $result['client_secret'] ?? null,
            'data' => $result,
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending_payment,paid,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        $order = OnlineOrder::where('etablissement_id', $this->getEtablissementId())->findOrFail($id);

        $update = [
            'status' => $request->status,
            'notes' => $request->filled('notes') ? $request->notes : $order->notes,
        ];

        if ($request->filled('payment_status')) {
            $update['payment_status'] = $request->payment_status;
        }

        if ($request->status === 'shipped' && !$order->shipped_at) {
            $update['shipped_at'] = now();
        }

        if ($request->status === 'delivered' && !$order->delivered_at) {
            $update['delivered_at'] = now();
        }

        if ($request->status === 'cancelled' && !$order->cancelled_at) {
            $update['cancelled_at'] = now();
        }

        $order->update($update);

        return response()->json([
            'success' => true,
            'message' => 'Statut de commande mis à jour.',
            'data' => $order->fresh(),
        ]);
    }

    private function getEtablissementId(): int
    {
        return auth()->user()->etablissement_id ?? (int) Etablissement::query()->value('id');
    }

    private function resolveCustomer(array $validated, int $etablissementId): Customer
    {
        if (!empty($validated['customer_id'])) {
            $customer = Customer::where('id', $validated['customer_id'])
                ->where('etablissement_id', $etablissementId)
                ->first();

            if (!$customer) {
                throw new \RuntimeException('Client introuvable pour cet établissement.');
            }

            return $customer;
        }

        $customerInput = $validated['customer'] ?? null;
        if (!$customerInput) {
            throw new \RuntimeException('Le client est requis (customer_id ou customer).');
        }

        if (empty($customerInput['nom']) && empty($customerInput['entreprise_nom'])) {
            throw new \RuntimeException('Le nom client est requis.');
        }

        return Customer::create([
            'etablissement_id' => $etablissementId,
            'type' => $customerInput['type'] ?? (!empty($customerInput['entreprise_nom']) ? 'entreprise' : 'particulier'),
            'prenom' => $customerInput['prenom'] ?? null,
            'nom' => $customerInput['nom'] ?? null,
            'email' => $customerInput['email'] ?? null,
            'telephone' => $customerInput['telephone'] ?? null,
            'entreprise_nom' => $customerInput['entreprise_nom'] ?? null,
            'adresse' => $customerInput['adresse'] ?? null,
            'code_postal' => $customerInput['code_postal'] ?? null,
            'ville' => $customerInput['ville'] ?? null,
            'pays' => $customerInput['pays'] ?? 'France',
        ]);
    }

    private function createInvoiceFromOrder(OnlineOrder $order): Invoice
    {
        if ($order->invoice_id) {
            return Invoice::findOrFail($order->invoice_id);
        }

        $customer = $order->customer;
        if (!$customer) {
            throw new \RuntimeException('Impossible de générer une facture sans client.');
        }

        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'etablissement_id' => $order->etablissement_id,
            'client_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays((int) ($customer->delai_paiement_jours ?? 0))->toDateString(),
            'subtotal' => $order->subtotal_ttc,
            'shipping_fees' => $order->shipping_amount,
            'administration_fees' => 0,
            'discount_percentage' => 0,
            'discount_amount' => $order->discount_amount,
            'tax_total' => $order->tax_total,
            'total' => $order->total,
            'paid_amount' => 0,
            'remaining_amount' => $order->total,
            'taxes_breakdown' => [],
            'status' => 'en_attente',
            'client_name' => $customer->nom_complet,
            'client_address' => $customer->adresse,
            'client_zipcode' => $customer->code_postal,
            'client_city' => $customer->ville,
            'client_country' => $customer->pays ?? 'France',
            'client_vat_number' => $customer->no_tva,
            'notes' => $order->notes,
            'metadata' => ['online_order_id' => $order->id],
        ]);

        $lineNumber = 1;
        foreach ($order->items as $item) {
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'line_number' => $lineNumber++,
                'description' => $item->product_name,
                'type' => 'produit',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price_ttc,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'subtotal' => $item->line_subtotal_ttc,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'total' => $item->line_total,
                'metadata' => ['online_order_item_id' => $item->id],
            ]);
        }

        return $invoice;
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (OnlineOrder::where('order_number', $number)->exists());

        return $number;
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Invoice::where('invoice_number', $number)->exists());

        return $number;
    }
}
