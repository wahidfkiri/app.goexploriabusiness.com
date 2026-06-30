@php
    $submitUrl = route('billing.requests.public.submit', ['etablissementId' => $etablissement->id]);
    $currency = $settings->currency ?: 'CAD';
    $siteName = $etablissement->name ?? $etablissement->nom ?? 'Go Exploria Business';
@endphp

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Demande de facturation - {{ $siteName }}</title>
    <style>
        :root { --blue: #2563eb; --ink: #0f172a; --muted: #64748b; --line: #dbe3ee; --soft: #f3f6fa; --ok: #16a34a; --danger: #dc2626; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: var(--soft); color: #172033; }
        .page { min-height: 100vh; padding: 28px; }
        .shell { max-width: 1180px; margin: 0 auto; display: grid; gap: 18px; }
        .head { display: flex; justify-content: space-between; align-items: center; gap: 18px; padding: 22px; background: linear-gradient(135deg, #fff 0%, #f8fbff 100%); border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 14px 32px rgba(15, 23, 42, .06); }
        .kicker { margin: 0 0 6px; color: var(--blue); text-transform: uppercase; font-size: 12px; font-weight: 850; }
        h1 { margin: 0; color: var(--ink); font-size: clamp(26px, 4vw, 40px); line-height: 1.05; letter-spacing: 0; }
        .subtitle { color: var(--muted); margin-top: 8px; font-weight: 650; }
        .layout { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 18px; align-items: start; }
        .panel { background: #fff; border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); overflow: hidden; }
        .panel-head { padding: 16px 18px; border-bottom: 1px solid #e2e8f0; background: #fbfdff; }
        .panel-head h2 { margin: 0; font-size: 19px; color: var(--ink); }
        .service-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; padding: 18px; }
        .service-card { position: relative; display: grid; grid-template-rows: auto 1fr auto; min-height: 260px; border: 1px solid #dbe3ee; border-radius: 8px; overflow: hidden; background: #fff; cursor: pointer; transition: .16s ease; }
        .service-card:hover { border-color: #9db7ec; box-shadow: 0 14px 30px rgba(37, 99, 235, .1); transform: translateY(-1px); }
        .service-card.selected { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
        .service-card input[type="checkbox"] { position: absolute; top: 12px; right: 12px; width: 20px; height: 20px; z-index: 2; accent-color: var(--blue); }
        .service-media { height: 132px; background: #e2e8f0 center/cover no-repeat; display: grid; place-items: center; color: #64748b; font-size: 28px; }
        .service-body { padding: 14px; display: grid; gap: 8px; align-content: start; }
        .service-body h3 { margin: 0; font-size: 18px; line-height: 1.25; color: var(--ink); }
        .service-body p { margin: 0; color: var(--muted); line-height: 1.45; }
        .service-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px; border-top: 1px solid #eef2f7; background: #fbfdff; }
        .price { font-size: 19px; font-weight: 850; color: var(--ink); }
        .qty { display: flex; align-items: center; gap: 8px; font-weight: 750; color: var(--muted); }
        .qty input { width: 72px; border: 1px solid #cbd5e1; border-radius: 7px; padding: 8px; min-height: 38px; font-weight: 750; }
        .form-panel { position: sticky; top: 18px; }
        .form-body { padding: 18px; display: grid; gap: 14px; }
        label { display: grid; gap: 6px; color: #475569; font-weight: 800; }
        label span { color: #64748b; font-size: 12px; text-transform: uppercase; }
        input, textarea { width: 100%; border: 1px solid #cbd5e1; border-radius: 7px; padding: 11px 12px; min-height: 42px; background: #fff; color: #172033; font-weight: 650; }
        textarea { resize: vertical; }
        input:focus, textarea:focus { outline: 0; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
        .two { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .summary { margin-top: 2px; padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; display: grid; gap: 8px; }
        .summary-row { display: flex; justify-content: space-between; gap: 12px; color: #334155; font-weight: 750; }
        .summary-row.total { padding-top: 8px; border-top: 1px solid #dbe3ee; color: var(--ink); font-size: 18px; font-weight: 850; }
        .btn { border: 0; border-radius: 7px; min-height: 46px; padding: 12px 16px; font-weight: 850; cursor: pointer; transition: .16s ease; }
        .btn-primary { background: var(--blue); color: #fff; box-shadow: 0 12px 24px rgba(37, 99, 235, .18); }
        .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
        .btn-primary:disabled { opacity: .65; cursor: not-allowed; transform: none; }
        .message { display: none; padding: 12px 14px; border-radius: 8px; font-weight: 750; }
        .message.success { display: block; background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .message.error { display: block; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .empty { padding: 34px; text-align: center; color: var(--muted); }
        @media (max-width: 980px) { .layout { grid-template-columns: 1fr; } .form-panel { position: static; } }
        @media (max-width: 720px) { .page { padding: 14px; } .head { display: grid; padding: 16px; } .service-grid, .two { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main class="page">
        <div class="shell">
            <header class="head">
                <div>
                    <p class="kicker">Demande de facturation</p>
                    <h1>{{ $siteName }}</h1>
                    <div class="subtitle">Selectionnez les options souhaitees, puis envoyez votre demande.</div>
                </div>
            </header>

            <div class="layout">
                <section class="panel">
                    <div class="panel-head">
                        <h2>Options disponibles</h2>
                    </div>
                    @if($services->isEmpty())
                        <div class="empty">Aucune option disponible pour le moment.</div>
                    @else
                        <div class="service-grid" id="servicesGrid">
                            @foreach($services as $service)
                                <label class="service-card" data-service-card="{{ $service->id }}">
                                    <input type="checkbox" class="service-check" value="{{ $service->id }}">
                                    <div class="service-media" @if($service->image_url) style="background-image:url('{{ $service->image_url }}')" @endif>
                                        @unless($service->image_url)
                                            <span>+</span>
                                        @endunless
                                    </div>
                                    <div class="service-body">
                                        <h3>{{ $service->title }}</h3>
                                        @if($service->description)
                                            <p>{{ \Illuminate\Support\Str::limit($service->description, 220) }}</p>
                                        @endif
                                    </div>
                                    <div class="service-foot">
                                        <span class="price">{{ number_format((float) $service->unit_price, 2, ',', ' ') }} {{ $currency === 'CAD' ? '$' : $currency }}</span>
                                        <span class="qty">Qt&eacute; <input type="number" class="service-qty" data-service-qty="{{ $service->id }}" min="1" value="1"></span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </section>

                <aside class="panel form-panel">
                    <div class="panel-head">
                        <h2>Vos informations</h2>
                    </div>
                    <form id="billingRequestForm" class="form-body">
                        <div id="formMessage" class="message"></div>
                        <label>
                            <span>Nom complet *</span>
                            <input type="text" name="name" required maxlength="191">
                        </label>
                        <label>
                            <span>Email *</span>
                            <input type="email" name="email" required maxlength="191">
                        </label>
                        <div class="two">
                            <label>
                                <span>Telephone</span>
                                <input type="text" name="phone" maxlength="50">
                            </label>
                            <label>
                                <span>Entreprise</span>
                                <input type="text" name="company" maxlength="191">
                            </label>
                        </div>
                        <label>
                            <span>Adresse</span>
                            <input type="text" name="address" maxlength="255">
                        </label>
                        <div class="two">
                            <label>
                                <span>Ville</span>
                                <input type="text" name="city" maxlength="120">
                            </label>
                            <label>
                                <span>Code postal</span>
                                <input type="text" name="zipcode" maxlength="30">
                            </label>
                        </div>
                        <label>
                            <span>Message</span>
                            <textarea name="message" rows="4"></textarea>
                        </label>

                        <div class="summary">
                            <div class="summary-row"><span>Sous-total</span><strong id="subtotalAmount">0,00 $</strong></div>
                            <div class="summary-row"><span>Remise</span><strong id="discountAmount">0,00 $</strong></div>
                            <div class="summary-row"><span>Taxes</span><strong id="taxAmount">0,00 $</strong></div>
                            <div class="summary-row total"><span>Total</span><strong id="totalAmount">0,00 $</strong></div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitRequestBtn">Envoyer la demande</button>
                    </form>
                </aside>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const services = @json($servicesPayload);
            const pricingSettings = @json($pricingSettings);
            const submitUrl = @json($submitUrl);
            const csrf = @json(csrf_token());
            const currency = @json($currency);
            const serviceMap = new Map(services.map(item => [Number(item.id), item]));
            const money = new Intl.NumberFormat('fr-CA', { style: 'currency', currency });
            const message = document.getElementById('formMessage');

            const selectedItems = () => Array.from(document.querySelectorAll('.service-check:checked')).map(input => {
                const id = Number(input.value);
                const qtyInput = document.querySelector(`[data-service-qty="${id}"]`);
                return { service_id: id, quantity: Math.max(1, Number(qtyInput?.value || 1)) };
            });

            const updateTotals = () => {
                let subtotal = 0;
                let taxes = 0;
                selectedItems().forEach(item => {
                    const service = serviceMap.get(item.service_id);
                    if (!service) return;
                    const lineSubtotal = Number(service.unit_price || 0) * item.quantity;
                    const taxRate = pricingSettings.global_tax_rate !== null && pricingSettings.global_tax_rate !== undefined
                        ? Number(pricingSettings.global_tax_rate || 0)
                        : Number(service.tax_rate || 0);
                    subtotal += lineSubtotal;
                    taxes += lineSubtotal * (taxRate / 100);
                });
                const discountConfig = pricingSettings.discount || {};
                const discountValue = Number(discountConfig.value || 0);
                const discount = discountValue <= 0 ? 0 : Math.min(subtotal, discountConfig.type === 'fixed' ? discountValue : subtotal * discountValue / 100);
                document.getElementById('subtotalAmount').textContent = money.format(subtotal);
                document.getElementById('discountAmount').textContent = `-${money.format(discount)}`;
                document.getElementById('taxAmount').textContent = money.format(taxes);
                document.getElementById('totalAmount').textContent = money.format(subtotal - discount + taxes);
            };

            document.querySelectorAll('.service-check').forEach(input => {
                input.addEventListener('change', () => {
                    input.closest('.service-card')?.classList.toggle('selected', input.checked);
                    updateTotals();
                });
            });
            document.querySelectorAll('.service-qty').forEach(input => input.addEventListener('input', updateTotals));

            document.getElementById('billingRequestForm').addEventListener('submit', async event => {
                event.preventDefault();
                message.className = 'message';
                message.textContent = '';

                const items = selectedItems();
                if (!items.length) {
                    message.className = 'message error';
                    message.textContent = 'Veuillez selectionner au moins une option.';
                    return;
                }

                const formData = new FormData(event.currentTarget);
                const payload = Object.fromEntries(formData.entries());
                payload.country = payload.country || 'Canada';
                payload.items = items;

                const button = document.getElementById('submitRequestBtn');
                button.disabled = true;
                button.textContent = 'Envoi en cours...';
                try {
                    const response = await fetch(submitUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify(payload),
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Erreur lors de l envoi.');
                    }
                    event.currentTarget.reset();
                    document.querySelectorAll('.service-check').forEach(input => {
                        input.checked = false;
                        input.closest('.service-card')?.classList.remove('selected');
                    });
                    updateTotals();
                    message.className = 'message success';
                    message.textContent = `${result.message} Numero: ${result.data?.request_number || ''}`;
                } catch (error) {
                    message.className = 'message error';
                    message.textContent = error.message || 'Erreur lors de l envoi.';
                } finally {
                    button.disabled = false;
                    button.textContent = 'Envoyer la demande';
                }
            });

            updateTotals();
        });
    </script>
</body>
</html>
