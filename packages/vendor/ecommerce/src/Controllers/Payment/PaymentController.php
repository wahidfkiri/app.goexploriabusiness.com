<?php

namespace Vendor\Ecommerce\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\BankDetail;
use App\Models\Etablissement;
use App\Models\Invoice;
use App\Models\OnlineOrder;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Vendor\Ecommerce\Services\Payment\PaymentService;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        if (!$request->filled('invoice_id') && !$request->filled('order_id')) {
            return response()->json([
                'success' => false,
                'message' => 'invoice_id ou order_id est requis.',
            ], 422);
        }

        $invoice = null;
        $order = null;

        if ($request->filled('invoice_id')) {
            $invoice = Invoice::findOrFail($request->invoice_id);
        }

        if ($request->filled('order_id')) {
            $order = OnlineOrder::with('invoice')->findOrFail($request->order_id);
            if (!$invoice && $order->invoice) {
                $invoice = $order->invoice;
            }
        }

        $amount = $order ? (float) $order->total : (float) $invoice->remaining_amount;
        $currency = $order?->currency ?: 'EUR';
        $customer = $order?->customer ?? $invoice?->client;
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Client introuvable pour ce paiement.',
            ], 422);
        }

        $gateway = PaymentGateway::where('is_active', true)
            ->where('code', $request->gateway)
            ->firstOrFail();

        $paymentService = new PaymentService($gateway);

        $result = $paymentService->createPayment(
            $amount,
            $currency,
            [
                'invoice_id' => $invoice?->id,
                'order_id' => $order?->id,
                'description' => $order
                    ? "Commande #{$order->order_number}"
                    : "Facture #{$invoice->invoice_number}",
                'email' => $customer?->email,
            ]
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erreur de paiement',
            ], 400);
        }

        $etablissementId = auth()->user()->etablissement_id ?? Etablissement::query()->value('id');

        PaymentTransaction::create([
            'etablissement_id' => $etablissementId,
            'invoice_id' => $invoice?->id,
            'online_order_id' => $order?->id,
            'client_id' => $customer?->id,
            'payment_gateway_id' => $gateway->id,
            'gateway_type' => $gateway->type,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'gateway_transaction_id' => $result['order_id'] ?? $result['payment_intent_id'] ?? null,
            'gateway_response' => $result['data'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => $result['approval_url'] ?? null,
            'client_secret' => $result['client_secret'] ?? null,
            'data' => $result,
        ]);
    }

    public function success(Request $request)
    {
        $token = $request->token;
        $payerId = $request->PayerID;

        $transaction = PaymentTransaction::where('gateway_transaction_id', $token)->first();

        if (!$transaction) {
            return redirect()->route('payments.failed')->with('error', 'Transaction non trouvée');
        }

        $gateway = $transaction->gateway;
        $paymentService = new PaymentService($gateway);

        $result = $paymentService->executePayment($token, $payerId);

        if (!$result['success']) {
            return redirect()->route('payments.failed')
                ->with('error', $result['message'] ?? 'Erreur lors du paiement');
        }

        $transaction->update([
            'status' => 'completed',
            'gateway_status' => 'completed',
            'gateway_payment_id' => $result['transaction_id'] ?? null,
        ]);

        $this->settleTransaction($transaction);

        if ($transaction->online_order_id) {
            return redirect()
                ->route('orders.show', $transaction->online_order_id)
                ->with('success', 'Paiement de la commande effectué avec succès.');
        }

        return redirect()
            ->route('invoices.show', $transaction->invoice_id)
            ->with('success', 'Paiement effectué avec succès.');
    }

    public function cancel(Request $request)
    {
        $token = $request->token;
        $transaction = PaymentTransaction::where('gateway_transaction_id', $token)->first();

        if ($transaction) {
            $transaction->update(['status' => 'failed', 'gateway_status' => 'cancelled']);

            if ($transaction->online_order_id) {
                $transaction->onlineOrder?->update([
                    'payment_status' => 'failed',
                ]);

                return redirect()
                    ->route('orders.show', $transaction->online_order_id)
                    ->with('error', 'Paiement de la commande annulé.');
            }
        }

        return redirect()->route('invoices.index')
            ->with('error', 'Paiement annulé');
    }

    public function failed()
    {
        return redirect()->route('invoices.index')->with('error', 'Le paiement a échoué.');
    }

    public function webhook(Request $request, $gateway)
    {
        $gatewayModel = PaymentGateway::where('code', $gateway)->firstOrFail();

        $paymentService = new PaymentService($gatewayModel);

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature') ?? $request->header('Paypal-Auth-Algo');

        $paymentService->handleWebhook($payload, $signature);

        return response()->json(['status' => 'success']);
    }

    public function getGateways()
    {
        $gateways = PaymentGateway::where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($gateway) {
                $service = new PaymentService($gateway);
                return [
                    'id' => $gateway->id,
                    'name' => $gateway->name,
                    'code' => $gateway->code,
                    'type' => $gateway->type,
                    'description' => $gateway->description,
                    'config' => $service->getClientConfig(),
                    'fees' => $gateway->fees,
                ];
            });

        $etablissementId = auth()->user()->etablissement_id ?? null;
        $bankDetails = $etablissementId
            ? BankDetail::where('etablissement_id', $etablissementId)->where('is_default', true)->first()
            : null;

        if ($bankDetails) {
            $gateways->push([
                'id' => 'bank',
                'name' => 'Virement bancaire',
                'code' => 'bank_transfer',
                'type' => 'bank_transfer',
                'description' => 'Paiement par virement bancaire',
                'bank_details' => $bankDetails,
                'fees' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $gateways,
        ]);
    }

    private function settleTransaction(PaymentTransaction $transaction): void
    {
        if (!$transaction->payment_id) {
            $payment = Payment::create([
                'etablissement_id' => $transaction->etablissement_id,
                'invoice_id' => $transaction->invoice_id,
                'online_order_id' => $transaction->online_order_id,
                'client_id' => $transaction->client_id,
                'payment_date' => now()->toDateString(),
                'amount' => $transaction->amount,
                'method' => $this->mapGatewayToPaymentMethod($transaction->gateway_type),
                'transaction_id' => $transaction->gateway_payment_id ?: $transaction->gateway_transaction_id,
                'status' => 'complete',
                'metadata' => [
                    'source' => 'gateway_callback',
                    'gateway_type' => $transaction->gateway_type,
                    'payment_transaction_id' => $transaction->id,
                ],
            ]);

            $transaction->update(['payment_id' => $payment->id]);
        }

        if ($transaction->onlineOrder) {
            $newStatus = $transaction->onlineOrder->status === 'pending_payment'
                ? 'processing'
                : $transaction->onlineOrder->status;

            $transaction->onlineOrder->update([
                'payment_status' => 'paid',
                'status' => $newStatus,
                'paid_at' => now(),
            ]);
        }
    }

    private function mapGatewayToPaymentMethod(string $gatewayType): string
    {
        return match ($gatewayType) {
            'stripe' => 'stripe',
            'paypal' => 'paypal',
            'bank_transfer' => 'virement',
            default => 'autres',
        };
    }
}
