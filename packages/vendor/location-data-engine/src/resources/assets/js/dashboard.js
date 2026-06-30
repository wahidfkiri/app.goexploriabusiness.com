(function () {
    const root = document.getElementById('ldeDashboardPage');
    if (!root) {
        return;
    }

    const form = document.getElementById('ldeScanForm');
    const country = document.getElementById('ldeCountry');
    const province = document.getElementById('ldeProvince');
    const region = document.getElementById('ldeRegion');
    const city = document.getElementById('ldeCity');
    const sector = document.getElementById('ldeSector');
    const noSession = document.getElementById('ldeNoSession');
    const sessionWrap = document.getElementById('ldeSessionStatus');
    let currentSessionId = null;
    let pollTimer = null;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function setOptions(select, items, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });
    }

    async function loadOptions(level, params, target, placeholder) {
        const url = new URL(`${root.dataset.referenceBase}/${level}`, window.location.origin);
        Object.entries(params || {}).forEach(([key, value]) => {
            if (value) url.searchParams.set(key, value);
        });

        const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        setOptions(target, payload.items || [], placeholder);
    }

    country.addEventListener('change', async () => {
        await loadOptions('provinces', { country_id: country.value }, province, 'Select province');
        setOptions(region, [], 'Select region');
        setOptions(city, [], 'Select city');
        setOptions(sector, [], 'Select sector');
    });

    province.addEventListener('change', async () => {
        await loadOptions('regions', { province_id: province.value }, region, 'Select region');
        setOptions(city, [], 'Select city');
        setOptions(sector, [], 'Select sector');
    });

    region.addEventListener('change', async () => {
        await loadOptions('cities', { region_id: region.value }, city, 'Select city');
        setOptions(sector, [], 'Select sector');
    });

    city.addEventListener('change', async () => {
        await loadOptions('sectors', { ville_id: city.value }, sector, 'Select sector');
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(form);

        const response = await fetch(root.dataset.scanEndpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const payload = await response.json();
        if (!payload.success) {
            alert(payload.message || 'Unable to start scan.');
            return;
        }

        currentSessionId = payload.session.id;
        noSession.classList.add('d-none');
        sessionWrap.classList.remove('d-none');
        startPolling();
    });

    function renderLogs(logs) {
        const container = document.getElementById('ldeLiveLogs');
        container.innerHTML = '';
        logs.forEach((log) => {
            const item = document.createElement('li');
            item.innerHTML = `<span class="badge text-bg-dark">${log.level}</span><div><strong>${log.event || 'event'}</strong><p>${log.message}</p></div>`;
            container.appendChild(item);
        });
    }

    async function pollStatus() {
        if (!currentSessionId) {
            return;
        }

        const response = await fetch(`${root.dataset.statusBase}/${currentSessionId}`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await response.json();
        if (!payload.success) {
            return;
        }

        const session = payload.session;
        document.getElementById('ldeSessionState').textContent = session.status;
        document.getElementById('ldeSessionTarget').textContent = session.target_label || '-';
        document.getElementById('ldeSessionResults').textContent = session.results_count;
        const progress = Number(session.progress_percentage || 0);
        const progressBar = document.getElementById('ldeProgressBar');
        progressBar.style.width = `${progress}%`;
        progressBar.textContent = `${progress}%`;
        renderLogs(payload.latest_logs || []);

        if (['completed', 'failed'].includes(session.status)) {
            clearInterval(pollTimer);
        }
    }

    function startPolling() {
        clearInterval(pollTimer);
        pollStatus();
        pollTimer = setInterval(pollStatus, 4000);
    }
})();
