<?php

namespace App\Http\Controllers;

use App\Models\BillingDiscount;
use App\Models\BillingRequest;
use App\Models\BillingRequestItem;
use App\Models\BillingRequestService;
use App\Models\BillingSetting;
use App\Models\Country;
use App\Models\Etablissement;
use App\Models\Plan;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

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
    
    public function payment()
    {
        $user = auth()->user();
        $etablissement = $user->etablissement;

        $settings = BillingSetting::first();
        $discounts = BillingDiscount::active()->get();
        $plans = BillingRequestService::with('tax')->active()->orderBy('sort_order')->orderBy('title')->get();
        $requests = BillingRequest::where('email', $user->email)->orWhere('company', $etablissement?->name)->latest()->take(5)->get();
        $requestItems = BillingRequestItem::whereIn('billing_request_id', $requests->pluck('id'))->with('service')->get();

        return view('payment', compact(
            'settings',
            'discounts',
            'plans',
            'requests',
            'requestItems',
            'etablissement'
        ));
    }

    public function createPayPalOrder(Request $request)
    {
        try {
            $data = $request->validate([
                'amount' => 'required|numeric|min:1',
                'plan_id' => 'nullable|integer',
                'plan_name' => 'required|string',
            ]);

            $amount = number_format((float) $data['amount'], 2, '.', '');
            $user = auth()->user();
            $etablissement = $user?->etablissement;
            $referenceId = uniqid('plan_', true);

            $provider = $this->makePayPalProvider();

            $order = $provider->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $referenceId,
                        'description' => 'Paiement plan: ' . $data['plan_name'],
                        'custom_id' => (string) ($etablissement?->id ?? $user?->id ?? $referenceId),
                        'amount' => [
                            'currency_code' => 'CAD',
                            'value' => $amount,
                        ],
                    ],
                ],
                'application_context' => [
                    'cancel_url' => route('billing.payment'),
                    'return_url' => route('billing.payment.paypal.capture'),
                    'brand_name' => 'Go Exploria Business',
                    'locale' => 'fr-FR',
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW',
                ],
            ]);

            if (($order['id'] ?? null) && ($order['status'] ?? null) === 'CREATED') {
                session([
                    'paypal_last_order_id' => $order['id'],
                    'paypal_orders.' . $order['id'] => [
                        'user_id' => $user?->id,
                        'etablissement_id' => $etablissement?->id,
                        'plan_id' => $data['plan_id'] ?? null,
                        'plan_name' => $data['plan_name'],
                        'amount' => $amount,
                        'currency' => 'CAD',
                    ],
                ]);

                return response()->json([
                    'success' => true,
                    'order_id' => $order['id'],
                    'orderID' => $order['id'],
                ]);
            }

            Log::error('PayPal order creation failed', ['response' => $order]);

            return response()->json([
                'success' => false,
                'message' => $order['message'] ?? 'Erreur lors de la creation du paiement PayPal.',
            ], 500);
        } catch (\Throwable $e) {
            Log::error('PayPal order creation exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur PayPal : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function capturePayPal(Request $request)
    {
        $orderId = $this->extractPayPalOrderId($request);

        if (!$orderId) {
            $message = 'ID de commande PayPal manquant. Veuillez relancer le paiement.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }

            return redirect()->route('billing.payment')->with('error', $message);
        }

        try {
            Log::info('PayPal capture started', ['order_id' => $orderId]);

            $provider = $this->makePayPalProvider();
            $result = $provider->capturePaymentOrder($orderId);

            Log::info('PayPal capture result', [
                'order_id' => $orderId,
                'response' => $result,
            ]);

            if ($this->paypalCaptureCompleted($result)) {
                $this->activateLinkedEtablissement($orderId);
                $request->session()->forget('paypal_orders.' . $orderId);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Paiement reussi ! Votre plan est active.',
                        'redirect_url' => route('billing.success'),
                    ]);
                }

                return redirect()->route('billing.success')
                    ->with('success', 'Paiement reussi ! Votre plan est active.');
            }

            Log::error('PayPal capture not completed', [
                'order_id' => $orderId,
                'status' => $result['status'] ?? 'unknown',
                'response' => $result,
            ]);

            $message = 'Le paiement n\'a pas ete complete. Statut: ' . ($result['status'] ?? 'inconnu');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }

            return redirect()->route('billing.payment')->with('error', $message);
        } catch (\Throwable $e) {
            Log::error('PayPal capture exception: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de paiement : ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('billing.payment')
                ->with('error', 'Erreur de paiement : ' . $e->getMessage());
        }
    }

    private function makePayPalProvider(): PayPalClient
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->setCurrency('CAD');
        $provider->getAccessToken();

        return $provider;
    }

    private function extractPayPalOrderId(Request $request): ?string
    {
        $orderId = $request->input('order_id')
            ?? $request->input('orderID')
            ?? $request->input('orderId')
            ?? $request->input('id')
            ?? $request->input('token')
            ?? $request->query('token')
            ?? session('paypal_last_order_id');

        return $orderId ? trim((string) $orderId) : null;
    }

    private function paypalCaptureCompleted(array $result): bool
    {
        $status = $result['status'] ?? null;

        if (in_array($status, ['COMPLETED', 'APPROVED'], true)) {
            return true;
        }

        $captureStatus = $result['purchase_units'][0]['payments']['captures'][0]['status'] ?? null;

        return in_array($captureStatus, ['COMPLETED', 'APPROVED'], true);
    }

    private function activateLinkedEtablissement(string $orderId): void
    {
        $user = auth()->user();
        $etablissement = $user?->etablissement;

        if (!$etablissement) {
            Log::warning('PayPal paid but no etablissement linked to user', [
                'order_id' => $orderId,
                'user_id' => $user?->id,
            ]);

            return;
        }

        $updates = ['is_active' => true];

        if (Schema::hasColumn('etablissements', 'subscription_status')) {
            $updates['subscription_status'] = 'active';
        }

        if (Schema::hasColumn('etablissements', 'subscription_expires_at')) {
            $updates['subscription_expires_at'] = now()->addMonth()->toDateString();
        }

        $etablissement->forceFill($updates)->save();

        Log::info('Etablissement activated after PayPal payment', [
            'order_id' => $orderId,
            'user_id' => $user->id,
            'etablissement_id' => $etablissement->id,
        ]);
    }

    private function legacyCreatePayPalOrder(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'plan_name' => 'required|string',
            ]);

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->setCurrency('CAD');
            $provider->getAccessToken();

            $order = $provider->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => uniqid('plan_', true),
                        'description' => 'Paiement plan: ' . $request->plan_name,
                        'amount' => [
                            'currency_code' => 'CAD',
                            'value' => number_format((float) $request->amount, 2, '.', ''),
                        ],
                    ],
                ],
                'application_context' => [
                    'cancel_url' => route('billing.payment'),
                    'return_url' => route('billing.payment.paypal.capture'),
                    'brand_name' => 'Go Exploria Business',
                    'locale' => 'fr-FR',
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW',
                ],
            ]);

            if (isset($order['id']) && $order['status'] === 'CREATED') {
                return response()->json([
                    'success' => true,
                    'order_id' => $order['id'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $order['message'] ?? 'Erreur lors de la création du paiement PayPal.',
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur PayPal : ' . $e->getMessage(),
            ], 500);
        }
    }

    private function legacyCapturePayPal(Request $request)
{
    $orderId = $request->order_id ?? $request->token;

    if (!$orderId) {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'ID de commande manquant.'], 400);
        }
        return redirect()->route('billing.payment')->with('error', 'ID de commande manquant.');
    }

    try {
        \Log::info('PayPal Capture - Order ID: ' . $orderId);
        
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->setCurrency('CAD');
        $provider->getAccessToken();
        $result = $provider->capturePaymentOrder($orderId);
        
        \Log::info('PayPal Capture Result: ', $result);
        
        // CORRECTION: Vérifier le statut correctement
        $isCompleted = isset($result['status']) && in_array($result['status'], ['COMPLETED', 'APPROVED']);
        
        // CORRECTION: Vérifier également si des captures sont présentes
        if (!$isCompleted && isset($result['purchase_units'][0]['payments']['captures'][0]['status'])) {
            $captureStatus = $result['purchase_units'][0]['payments']['captures'][0]['status'];
            $isCompleted = in_array($captureStatus, ['COMPLETED', 'APPROVED']);
        }
        
        if ($isCompleted) {
            // Activer l'établissement
            $user = auth()->user();
            if ($user && $user->etablissement) {
                $user->etablissement->is_active = true;
                $user->etablissement->save();
            }
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Paiement réussi !']);
            }
            return redirect()->route('billing.payment')->with('success', 'Paiement réussi ! Votre plan est activé.');
        }
        
        // Log l'erreur détaillée
        \Log::error('PayPal Capture Failed - Status: ' . ($result['status'] ?? 'unknown'), $result);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Le paiement n\'a pas été complété. Statut: ' . ($result['status'] ?? 'inconnu')], 400);
        }
        return redirect()->route('billing.payment')->with('error', 'Le paiement n\'a pas été complété. Statut: ' . ($result['status'] ?? 'inconnu'));
        
    } catch (\Throwable $e) {
        \Log::error('PayPal Capture Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Erreur de paiement : ' . $e->getMessage()], 500);
        }
        return redirect()->route('billing.payment')->with('error', 'Erreur de paiement : ' . $e->getMessage());
    }
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
