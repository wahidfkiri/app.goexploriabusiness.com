<?php

namespace Vendor\Ecommerce\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BillingDiscount;
use App\Models\BillingSetting;
use App\Models\Etablissement;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BillingSettingsController extends Controller
{
    /**
     * Affiche la page des paramÃ¨tres de facturation
     */
    public function index(Request $request)
    {
        // RÃ©cupÃ©rer les paramÃ¨tres globaux (sans etablissement_id)
        $settings = $this->getBillingSettings();

        return view('ecommerce::billing-settings.index', [
            'settings' => $settings,
            'taxes' => Tax::orderByDesc('is_default')->orderBy('rate')->get(),
            'discounts' => BillingDiscount::orderByDesc('is_default')->orderBy('name')->get(),
            'templateOptions' => $this->templateOptions(),
        ]);
    }

    /**
     * Met Ã  jour les paramÃ¨tres de facturation
     */
    public function update(Request $request): JsonResponse
    {
        $settings = $this->getBillingSettings();

        $validated = $request->validate([
            'hide_invoice_button' => ['nullable', 'boolean'],
            'taxes_enabled' => ['nullable', 'boolean'],
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'last_invoice_number' => ['required', 'string', 'max:50'],
            'invoice_number_length' => ['required', 'integer', 'min:3', 'max:12'],
            'quote_prefix' => ['required', 'string', 'max:20'],
            'last_quote_number' => ['required', 'string', 'max:50'],
            'quote_number_length' => ['required', 'integer', 'min:3', 'max:12'],
            'invoice_template' => ['required', Rule::in(array_keys($this->templateOptions()))],
            'quote_template' => ['required', Rule::in(array_keys($this->templateOptions()))],
            'currency' => ['required', 'string', 'size:3'],
            'locale' => ['required', 'string', 'max:10'],
            'invoice_due_label' => ['nullable', 'string', 'max:100'],
            'quote_validity_label' => ['nullable', 'string', 'max:100'],
            'tax_number_tps' => ['nullable', 'string', 'max:100'],
            'tax_number_tvq' => ['nullable', 'string', 'max:100'],
            'neq' => ['nullable', 'string', 'max:100'],
            'rcs_number' => ['nullable', 'string', 'max:100'],
            'siret' => ['nullable', 'string', 'max:100'],
            'default_shipping_fees' => ['nullable', 'numeric', 'min:0'],
            'default_administration_fees' => ['nullable', 'numeric', 'min:0'],
            'default_discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_discount_id' => ['nullable', 'integer', 'exists:billing_discounts,id'],
            'default_tax_ids' => ['nullable', 'array'],
            'default_tax_ids.*' => ['integer', 'exists:taxes,id'],
            'payment_deadline_days' => ['required', 'integer', 'min:0', 'max:365'],
            'quote_validity_days' => ['required', 'integer', 'min:0', 'max:365'],
            'cheque_order' => ['nullable', 'string', 'max:255'],
            'bank_details' => ['nullable', 'array'],
            'bank_details.bank_name' => ['nullable', 'string', 'max:255'],
            'bank_details.iban' => ['nullable', 'string', 'max:100'],
            'bank_details.bic' => ['nullable', 'string', 'max:100'],
            'payment_button_code' => ['nullable', 'string', 'max:255'],
            'enable_online_payment' => ['nullable', 'boolean'],
            'enable_partial_payments' => ['nullable', 'boolean'],
            'auto_send_invoice_pdf' => ['nullable', 'boolean'],
            'auto_convert_accepted_quote' => ['nullable', 'boolean'],
            'procedure' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'default_note' => ['nullable', 'string'],
            'invoice_footer_note' => ['nullable', 'string'],
            'quote_footer_note' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'string'],
            'legal_mentions' => ['nullable', 'string'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        // Traitement des champs boolÃ©ens
        foreach (['hide_invoice_button', 'taxes_enabled', 'enable_online_payment', 'enable_partial_payments', 'auto_send_invoice_pdf', 'auto_convert_accepted_quote'] as $field) {
            $validated[$field] = $request->boolean($field);
        }

        // Valeurs par dÃ©faut pour les tableaux
        $validated['default_tax_ids'] = $validated['default_tax_ids'] ?? [];
        $validated['default_discount_id'] = $validated['default_discount_id'] ?? null;
        $validated['bank_details'] = $validated['bank_details'] ?? [];

        // Gestion du logo
        if ($request->boolean('remove_logo')) {
            $validated['billing_logo_url'] = null;
        }

        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo si prÃ©sent
            if ($settings->billing_logo_url) {
                $this->deletePublicStorageUrl($settings->billing_logo_url);
            }

            $path = $request->file('logo')->store('billing/logos', 'public');
            $validated['billing_logo_url'] = url(Storage::disk('public')->url($path));
        }

        // Supprimer les champs non persistants
        unset($validated['logo'], $validated['remove_logo']);

        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'ParamÃ¨tres de facturation enregistrÃ©s avec succÃ¨s.',
            'data' => $settings->fresh(),
        ]);
    }

    /**
     * CrÃ©e une nouvelle taxe
     */
    public function storeTax(Request $request): JsonResponse
    {
        $validated = $this->validateTax($request);

        // Si cette taxe est dÃ©finie comme par dÃ©faut, dÃ©sactiver les autres
        if ($request->boolean('is_default')) {
            Tax::query()->update(['is_default' => false]);
        }

        $tax = Tax::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Taxe ajoutÃ©e avec succÃ¨s.',
            'data' => $tax,
        ]);
    }

    /**
     * Met Ã  jour une taxe
     */
    public function updateTax(Request $request, int $id): JsonResponse
    {
        $tax = Tax::findOrFail($id);
        $validated = $this->validateTax($request, $tax->id);

        // Si cette taxe est dÃ©finie comme par dÃ©faut, dÃ©sactiver les autres
        if ($request->boolean('is_default')) {
            Tax::where('id', '!=', $tax->id)->update(['is_default' => false]);
        }

        $tax->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Taxe mise Ã  jour avec succÃ¨s.',
            'data' => $tax->fresh(),
        ]);
    }

    /**
     * Supprime une taxe
     */
    public function destroyTax(Request $request, int $id): JsonResponse
    {
        $tax = Tax::findOrFail($id);

        // VÃ©rifier si la taxe est utilisÃ©e
        if ($tax->invoiceLines()->exists() || $tax->quoteLines()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette taxe est utilisÃ©e dans des documents et ne peut pas Ãªtre supprimÃ©e.',
            ], 409);
        }

        $tax->delete();

        return response()->json([
            'success' => true,
            'message' => 'Taxe supprimÃ©e avec succÃ¨s.',
        ]);
    }

    /**
     * CrÃ©e une nouvelle remise
     */
    public function storeDiscount(Request $request): JsonResponse
    {
        $validated = $this->validateDiscount($request);

        // Si cette remise est dÃ©finie comme par dÃ©faut, dÃ©sactiver les autres
        if ($request->boolean('is_default')) {
            BillingDiscount::where('is_default', true)->update(['is_default' => false]);
        }

        $discount = BillingDiscount::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Remise ajoutÃ©e avec succÃ¨s.',
            'data' => $discount,
        ]);
    }

    /**
     * Met Ã  jour une remise
     */
    public function updateDiscount(Request $request, int $id): JsonResponse
    {
        $discount = BillingDiscount::findOrFail($id);
        $validated = $this->validateDiscount($request, $discount->id);

        // Si cette remise est dÃ©finie comme par dÃ©faut, dÃ©sactiver les autres
        if ($request->boolean('is_default')) {
            BillingDiscount::where('id', '!=', $discount->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $discount->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Remise mise Ã  jour avec succÃ¨s.',
            'data' => $discount->fresh(),
        ]);
    }

    /**
     * Supprime une remise
     */
    public function destroyDiscount(Request $request, int $id): JsonResponse
    {
        $discount = BillingDiscount::findOrFail($id);

        // Supprimer la rÃ©fÃ©rence dans les paramÃ¨tres de facturation
        BillingSetting::where('default_discount_id', $discount->id)->update(['default_discount_id' => null]);

        $discount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Remise supprimÃ©e avec succÃ¨s.',
        ]);
    }

    /**
     * Valide les donnÃ©es d'une taxe
     */
    protected function validateTax(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('taxes', 'code')->ignore($ignoreId)],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'type' => ['required', 'in:tps,tvq,tva,autres'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        // Pour une nouvelle taxe, active par dÃ©faut. Pour une mise Ã  jour, conserver la valeur ou utiliser false
        $validated['is_active'] = $request->boolean('is_active', $ignoreId === null);

        return $validated;
    }

    /**
     * Valide les donnÃ©es d'une remise
     */
    protected function validateDiscount(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        // VÃ©rification supplÃ©mentaire pour les remises en pourcentage
        if (($validated['type'] ?? 'percentage') === 'percentage' && (float) $validated['value'] > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'value' => 'La remise en pourcentage ne peut pas dÃ©passer 100%.',
            ]);
        }

        $validated['is_default'] = $request->boolean('is_default');
        // Pour une nouvelle remise, active par dÃ©faut. Pour une mise Ã  jour, conserver la valeur ou utiliser false
        $validated['is_active'] = $request->boolean('is_active', $ignoreId === null);

        return $validated;
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
     * Supprime un fichier stockÃ© dans le storage public
     */
    protected function deletePublicStorageUrl(?string $url): void
    {
        if (!$url) {
            return;
        }

        // Extraire le chemin du fichier depuis l'URL
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return;
        }

        // Supprimer le prÃ©fixe /storage/ pour obtenir le chemin relatif
        $relativePath = str_replace('/storage/', '', $path);

        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

    /**
     * Liste des options de templates disponibles
     */
    protected function templateOptions(): array
    {
        return [
            'classic' => 'Classique',
            'modern' => 'Moderne',
            'compact' => 'Compact',
            'corporate' => 'Corporate',
        ];
    }
}
