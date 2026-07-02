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
        $services = BillingRequestService::with('tax')->active()->orderBy('sort_order')->orderBy('title')->get();
        $discounts = BillingDiscount::active()->get();
        $plans = Plan::active()->ordered()->get();
        $requests = BillingRequest::where('email', $user->email)->orWhere('company', $etablissement?->name)->latest()->take(5)->get();
        $requestItems = BillingRequestItem::whereIn('billing_request_id', $requests->pluck('id'))->with('service')->get();

        return view('payment', compact(
            'settings',
            'services',
            'discounts',
            'plans',
            'requests',
            'requestItems',
            'etablissement'
        ));
    }

    public function createPayPalOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'plan_name' => 'required|string',
        ]);

        $provider = new PayPalClient;
        $mode = config('services.paypal.mode', 'sandbox');
        $clientId = $mode === 'live'
            ? config('services.paypal.client_id') ?? env('PAYPAL_LIVE_CLIENT_ID')
            : config('services.paypal.client_id') ?? env('PAYPAL_SANDBOX_CLIENT_ID');
        $secret = $mode === 'live'
            ? config('services.paypal.client_secret') ?? env('PAYPAL_LIVE_CLIENT_SECRET')
            : config('services.paypal.client_secret') ?? env('PAYPAL_SANDBOX_CLIENT_SECRET');

        $provider->setApiCredentials([
            'mode' => $mode,
            'sandbox' => [
                'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', $clientId),
                'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', $secret),
            ],
            'live' => [
                'client_id' => env('PAYPAL_LIVE_CLIENT_ID', $clientId),
                'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', $secret),
            ],
            'payment_action' => 'capture',
            'currency' => 'CAD',
            'notify_url' => route('payments.webhook.paypal'),
            'locale' => 'fr_CA',
            'validate_ssl' => true,
        ]);

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
            $approvalUrl = collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null;
            return response()->json([
                'success' => true,
                'order_id' => $order['id'],
                'approval_url' => $approvalUrl,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $order['message'] ?? 'Erreur lors de la création du paiement PayPal.',
        ], 500);
    }

    public function capturePayPal(Request $request)
    {
        $token = $request->token;

        if (!$token) {
            return redirect()->route('billing.payment')->with('error', 'Token de paiement manquant.');
        }

        $provider = new PayPalClient;
        $mode = config('services.paypal.mode', 'sandbox');
        $clientId = $mode === 'live'
            ? config('services.paypal.client_id') ?? env('PAYPAL_LIVE_CLIENT_ID')
            : config('services.paypal.client_id') ?? env('PAYPAL_SANDBOX_CLIENT_ID');
        $secret = $mode === 'live'
            ? config('services.paypal.client_secret') ?? env('PAYPAL_LIVE_CLIENT_SECRET')
            : config('services.paypal.client_secret') ?? env('PAYPAL_SANDBOX_CLIENT_SECRET');

        $provider->setApiCredentials([
            'mode' => $mode,
            'sandbox' => [
                'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', $clientId),
                'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', $secret),
            ],
            'live' => [
                'client_id' => env('PAYPAL_LIVE_CLIENT_ID', $clientId),
                'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', $secret),
            ],
            'payment_action' => 'capture',
            'currency' => 'CAD',
            'notify_url' => route('payments.webhook.paypal'),
            'locale' => 'fr_CA',
            'validate_ssl' => true,
        ]);

        $provider->getAccessToken();
        $result = $provider->capturePaymentOrder($token);

        if (isset($result['status']) && $result['status'] === 'COMPLETED') {
            return redirect()->route('billing.payment')->with('success', 'Paiement réussi ! Votre plan est activé.');
        }

        return redirect()->route('billing.payment')->with('error', 'Le paiement a échoué. Veuillez réessayer.');
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