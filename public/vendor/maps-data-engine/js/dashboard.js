(function () {
    const root = document.getElementById('mdeDashboardPage');
    if (!root) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const form = document.getElementById('mdeScanForm');
    const country = document.getElementById('mdeCountry');
    const province = document.getElementById('mdeProvince');
    const region = document.getElementById('mdeRegion');
    const city = document.getElementById('mdeCity');
    let currentSessionId = null;
    let timer = null;

    function setOptions(select, items, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });
    }

    async function loadLevel(level, params, target, placeholder) {
        const url = new URL(`${root.dataset.referenceBase}/${level}`, window.location.origin);
        Object.entries(params).forEach(([key, value]) => value && url.searchParams.set(key, value));
        const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        setOptions(target, payload.items || [], placeholder);
    }

    country.addEventListener('change', async () => {
        await loadLevel('provinces', { country_id: country.value }, province, 'Select province');
        setOptions(region, [], 'Select region');
        setOptions(city, [], 'Select city');
    });

    province.addEventListener('change', async () => {
        await loadLevel('regions', { province_id: province.value }, region, 'Select region');
        setOptions(city, [], 'Select city');
    });

    region.addEventListener('change', async () => {
        await loadLevel('cities', { region_id: region.value }, city, 'Select city');
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const response = await fetch(root.dataset.scanEndpoint, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
        });
        const payload = await response.json();
        if (!payload.success) {
            alert(payload.message || 'Unable to start scrape.');
            return;
        }
        currentSessionId = payload.session.id;
        document.getElementById('mdeNoSession').classList.add('d-none');
        document.getElementById('mdeSessionBox').classList.remove('d-none');
        startPolling();
    });

    function renderLogs(logs) {
        const wrap = document.getElementById('mdeLiveLogs');
        wrap.innerHTML = '';
        logs.forEach((log) => {
            const li = document.createElement('li');
            li.innerHTML = `<span class="badge text-bg-dark">${log.level}</span><div><strong>${log.event || 'event'}</strong><p>${log.message}</p></div>`;
            wrap.appendChild(li);
        });
    }

    function updateQueueHint(session) {
        const hint = document.getElementById('mdeQueueHint');
        if (!hint) return;

        const isPending = session.status === 'pending';
        const isRunningWithoutProgress = session.status === 'running' && Number(session.segments_completed || 0) === 0;
        const isCompletedOrFailed = ['completed', 'failed'].includes(session.status);

        if (isCompletedOrFailed) {
            hint.classList.add('d-none');
            hint.textContent = '';
            return;
        }

        let message = '';
        if (isPending) {
            const createdAt = session.created_at ? new Date(session.created_at) : null;
            const elapsedMs = createdAt ? Date.now() - createdAt.getTime() : 0;
            const elapsedMinutes = elapsedMs > 0 ? Math.floor(elapsedMs / 60000) : 0;
            if (elapsedMinutes >= 2) {
                message = 'Queued: waiting for queue worker to pick up this session. If this persists, verify `queue:work` is running.';
            } else {
                message = 'Queued: waiting for worker. This is normal for a short moment after clicking Start scrape.';
            }
        } else if (isRunningWithoutProgress) {
            message = 'Worker is running and preparing the first segment. Results will appear after the first segment finishes.';
        }

        if (!message) {
            hint.classList.add('d-none');
            hint.textContent = '';
            return;
        }

        hint.textContent = message;
        hint.classList.remove('d-none');
    }

    async function poll() {
        if (!currentSessionId) return;
        const response = await fetch(`${root.dataset.statusBase}/${currentSessionId}`, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        const session = payload.session;
        document.getElementById('mdeStatus').textContent = session.status;
        document.getElementById('mdeTarget').textContent = session.target_label || '-';
        document.getElementById('mdeResults').textContent = session.results_count || 0;
        document.getElementById('mdeCaptcha').textContent = session.captcha_incidents || 0;
        const percentage = Number((session.progress || {}).percentage || 0);
        const bar = document.getElementById('mdeProgressBar');
        bar.style.width = `${percentage}%`;
        bar.textContent = `${percentage}%`;
        updateQueueHint(session);
        renderLogs(payload.latest_logs || []);
        if (['completed', 'failed'].includes(session.status)) clearInterval(timer);
    }

    async function loadInfrastructure() {
        const response = await fetch(root.dataset.infrastructureEndpoint, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        document.getElementById('mdeProxyPool').textContent = (payload.proxies || []).length;
        document.getElementById('mdeBrowserPool').textContent = (payload.browser_sessions || []).length;
    }

    function startPolling() {
        clearInterval(timer);
        poll();
        timer = setInterval(poll, 5000);
    }

    loadInfrastructure();
})();
