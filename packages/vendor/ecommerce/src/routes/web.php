<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Vendor\Ecommerce\Controllers\BillingRequestController;
use Vendor\Ecommerce\Controllers\BillingSettingsController;
use Vendor\Ecommerce\Controllers\Admin\PaymentGatewayController;
use Vendor\Ecommerce\Controllers\InvoiceController;
use Vendor\Ecommerce\Controllers\OrderController;
use Vendor\Ecommerce\Controllers\Payment\PaymentController as OnlinePaymentController;
use Vendor\Ecommerce\Controllers\PaymentController;
use Vendor\Ecommerce\Controllers\ProductController;
use Vendor\Ecommerce\Controllers\QuoteController;

Auth::routes();

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('ecommerce')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/statistics', [ProductController::class, 'statistics'])->name('products.statistics');
        Route::get('/products/export/{format}', [ProductController::class, 'export'])->name('products.export');
        Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/generate-reference', [ProductController::class, 'generateReference'])->name('products.generate-reference');
        Route::post('/products/calculate-price', [ProductController::class, 'calculatePrice'])->name('products.calculate-price');
        Route::post('/products/check-sku', [ProductController::class, 'checkSku'])->name('products.check-sku');
        Route::post('/products/upload-image', [ProductController::class, 'uploadImage'])->name('products.upload-image');
        Route::delete('/products/{product}/force', [ProductController::class, 'forceDestroy'])->name('products.force-destroy');
        Route::post('/products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('/orders/{order}/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
    });

    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/statistics', [PaymentController::class, 'statistics'])->name('payments.statistics');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::post('/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.update-status');
        Route::post('/{payment}/note', [PaymentController::class, 'addNote'])->name('payments.add-note');
        Route::get('/{payment}/receipt', [PaymentController::class, 'downloadReceipt'])->name('payments.receipt');
        Route::post('/{payment}/send-receipt', [PaymentController::class, 'sendReceipt'])->name('payments.send-receipt');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::post('/checkout', [OnlinePaymentController::class, 'checkout'])->name('checkout');
        Route::get('/success', [OnlinePaymentController::class, 'success'])->name('success');
        Route::get('/cancel', [OnlinePaymentController::class, 'cancel'])->name('cancel');
        Route::get('/failed', [OnlinePaymentController::class, 'failed'])->name('failed');
        Route::get('/gateways', [OnlinePaymentController::class, 'getGateways'])->name('gateways');
    });

    Route::prefix('admin/payment')->name('admin.payment.')->group(function () {
        Route::get('gateways', [PaymentGatewayController::class, 'index'])->name('gateways');
        Route::get('get-config', [PaymentGatewayController::class, 'getConfig'])->name('get-config');
        Route::post('save-config', [PaymentGatewayController::class, 'saveConfig'])->name('save-config');
        Route::post('test/stripe', [PaymentGatewayController::class, 'testStripe'])->name('test-stripe');
        Route::post('test/paypal', [PaymentGatewayController::class, 'testPayPal'])->name('test-paypal');
        Route::post('test/{gateway}', [PaymentGatewayController::class, 'testConnection'])->name('test');
    });

    Route::get('invoices/statistics/data', [InvoiceController::class, 'statistics'])->name('invoices.statistics');
    Route::get('billing/settings', [BillingSettingsController::class, 'index'])->name('billing.settings.index');
    Route::post('billing/settings', [BillingSettingsController::class, 'update'])->name('billing.settings.update');
    Route::post('billing/settings/taxes', [BillingSettingsController::class, 'storeTax'])->name('billing.settings.taxes.store');
    Route::put('billing/settings/taxes/{id}', [BillingSettingsController::class, 'updateTax'])->name('billing.settings.taxes.update');
    Route::delete('billing/settings/taxes/{id}', [BillingSettingsController::class, 'destroyTax'])->name('billing.settings.taxes.destroy');
    Route::post('billing/settings/discounts', [BillingSettingsController::class, 'storeDiscount'])->name('billing.settings.discounts.store');
    Route::put('billing/settings/discounts/{id}', [BillingSettingsController::class, 'updateDiscount'])->name('billing.settings.discounts.update');
    Route::delete('billing/settings/discounts/{id}', [BillingSettingsController::class, 'destroyDiscount'])->name('billing.settings.discounts.destroy');

    Route::get('billing/request-services', [BillingRequestController::class, 'servicesIndex'])->name('billing.request-services.index');
    Route::post('billing/request-services', [BillingRequestController::class, 'servicesStore'])->name('billing.request-services.store');
    Route::put('billing/request-services/{id}', [BillingRequestController::class, 'servicesUpdate'])->name('billing.request-services.update');
    Route::delete('billing/request-services/{id}', [BillingRequestController::class, 'servicesDestroy'])->name('billing.request-services.destroy');
    
    Route::get('billing/requests', [BillingRequestController::class, 'requestsIndex'])->name('billing.requests.index');
    Route::get('billing/requests/{id}', [BillingRequestController::class, 'requestsShow'])->name('billing.requests.show');
    Route::patch('billing/requests/{id}/status', [BillingRequestController::class, 'requestsUpdateStatus'])->name('billing.requests.status');
    Route::post('billing/requests/{id}/quote', [BillingRequestController::class, 'convertToQuote'])->name('billing.requests.quote');
    Route::post('billing/requests/{id}/invoice', [BillingRequestController::class, 'convertToInvoice'])->name('billing.requests.invoice');
    Route::post('billing/requests/{id}/send-invoice', [BillingRequestController::class, 'sendInvoice'])->name('billing.requests.send-invoice');
    
    // ROUTES DE SUPPRESSION - AJOUTER CES LIGNES
    Route::delete('billing/requests/{id}', [BillingRequestController::class, 'requestsDestroy'])->name('billing.requests.destroy');
    Route::post('billing/requests/bulk-delete', [BillingRequestController::class, 'requestsBulkDestroy'])->name('billing.requests.bulk-destroy');
    Route::get('billing/requests/{id}/can-delete', [BillingRequestController::class, 'requestsCanDelete'])->name('billing.requests.can-delete');

    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'sendEmail'])->name('invoices.send');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    Route::resource('invoices', InvoiceController::class);

    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'downloadPdf'])->name('quotes.pdf');
    Route::post('quotes/{quote}/convert-to-invoice', [QuoteController::class, 'convertToInvoice'])->name('quotes.convert-to-invoice');
    Route::resource('quotes', QuoteController::class);
});

// Webhooks (sans auth, à protéger via signature côté provider)
Route::post('/webhook/paypal', [OnlinePaymentController::class, 'webhook'])
    ->defaults('gateway', 'paypal')
    ->name('payments.webhook.paypal');

Route::post('/webhook/stripe', [OnlinePaymentController::class, 'webhook'])
    ->defaults('gateway', 'stripe')
    ->name('payments.webhook.stripe');

Route::middleware(['web'])->group(function () {
    Route::get('billing-request/{etablissementId}', [BillingRequestController::class, 'publicForm'])->name('billing.requests.public.form');
    Route::get('billing-request/{etablissementId}/options', [BillingRequestController::class, 'publicOptions'])->name('billing.requests.public.options');
    Route::post('billing-request/{etablissementId}', [BillingRequestController::class, 'publicSubmit'])->name('billing.requests.public.submit');
});