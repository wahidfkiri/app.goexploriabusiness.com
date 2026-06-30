(function () {
    const root = document.getElementById('mdeResultsPage');
    if (!root) return;
    const modal = new bootstrap.Modal(document.getElementById('mdeDetailModal'));
    const state = { page: 1, lastPage: 1, view: 'cards' };

    function params() {
        return {
            search: document.getElementById('mdeSearch').value,
            country: document.getElementById('mdeCountryFilter').value,
            province: document.getElementById('mdeProvinceFilter').value,
            city: document.getElementById('mdeCityFilter').value,
            category: document.getElementById('mdeCategoryFilter').value,
            per_page: document.getElementById('mdePerPage').value,
            page: state.page,
        };
    }

    async function load(reset = false) {
        if (reset) state.page = 1;
        const url = new URL(root.dataset.resultsEndpoint, window.location.origin);
        Object.entries(params()).forEach(([key, value]) => value && url.searchParams.set(key, value));
        const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        const pager = payload.results;
        state.lastPage = pager.last_page;
        renderCards(pager.data, reset);
        renderTable(pager.data, reset);
        document.getElementById('mdeResultsCount').textContent = `${pager.total} results`;
        document.getElementById('mdeResultsPager').textContent = `Page ${pager.current_page} / ${pager.last_page}`;
        document.getElementById('mdeLoadMore').classList.toggle('d-none', state.view !== 'cards' || pager.current_page >= pager.last_page);
    }

    function renderCards(items, reset) {
        const wrap = document.getElementById('mdeCards');
        if (reset) wrap.innerHTML = '';
        items.forEach((item) => {
            const card = document.createElement('article');
            card.className = 'mde-result-card';
            card.innerHTML = `<div class="mde-result-card-top"><div><h3>${item.name}</h3><p>${item.address || '-'}</p></div><span class="badge text-bg-dark">${item.city || '-'}</span></div><div class="mde-result-meta"><span><i class="fas fa-star text-warning"></i> ${item.rating || '-'}</span><span><i class="fas fa-comments"></i> ${item.reviews_count || 0}</span><span><i class="fas fa-globe"></i> ${(item.categories || []).join(', ')}</span></div><div class="d-flex justify-content-between align-items-center"><a href="${item.website || '#'}" ${item.website ? 'target="_blank"' : 'class="disabled"'}>Website</a><button class="btn btn-sm btn-outline-primary" data-id="${item.id}">Details</button></div>`;
            wrap.appendChild(card);
        });
    }

    function renderTable(items, reset) {
        const body = document.getElementById('mdeTableBody');
        if (reset) body.innerHTML = '';
        items.forEach((item) => {
            const row = document.createElement('tr');
            row.innerHTML = `<td>${item.name}</td><td>${item.address || '-'}</td><td>${item.website || '-'}</td><td>${item.phone || '-'}</td><td>${item.rating || '-'}</td><td><button class="btn btn-sm btn-outline-primary" data-id="${item.id}">Details</button></td>`;
            body.appendChild(row);
        });
    }

    async function details(id) {
        const response = await fetch(`${root.dataset.detailBase}/${id}`, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        const item = payload.data;
        document.getElementById('mdeDetailBody').innerHTML = `<div class="row g-4"><div class="col-lg-8"><h3>${item.name}</h3><p>${item.address || '-'}</p><p><strong>Website:</strong> ${item.website ? `<a href="${item.website}" target="_blank">${item.website}</a>` : '-'}</p><p><strong>Phone:</strong> ${item.phone || '-'}</p><p><strong>Categories:</strong> ${(item.categories || []).join(', ')}</p><p><strong>Maps URL:</strong> ${item.google_maps_url ? `<a href="${item.google_maps_url}" target="_blank">Open</a>` : '-'}</p><div class="mt-4"><strong>Reviews preview</strong>${(item.reviews_preview || []).map((review) => `<div class="mde-review-card mt-2"><strong>${review.author || 'Review'}</strong><p class="mb-0 mt-2">${review.text || 'No preview text.'}</p></div>`).join('') || '<p class="text-muted mt-2">No reviews preview.</p>'}</div></div><div class="col-lg-4"><div class="mde-micro-stat"><span>Country</span><strong>${item.country || '-'}</strong></div><div class="mde-micro-stat"><span>Province</span><strong>${item.province || '-'}</strong></div><div class="mde-micro-stat"><span>City</span><strong>${item.city || '-'}</strong></div><div class="mde-micro-stat"><span>Last scraped</span><strong>${item.last_scraped_at || '-'}</strong></div></div></div>`;
        modal.show();
    }

    document.getElementById('mdeApplyFilters').addEventListener('click', () => load(true));
    document.getElementById('mdeResetFilters').addEventListener('click', () => {
        ['mdeSearch', 'mdeCountryFilter', 'mdeProvinceFilter', 'mdeCityFilter'].forEach((id) => document.getElementById(id).value = '');
        document.getElementById('mdeCategoryFilter').value = '';
        load(true);
    });
    document.getElementById('mdeCardView').addEventListener('click', () => {
        state.view = 'cards';
        document.getElementById('mdeCards').classList.remove('d-none');
        document.getElementById('mdeTableWrap').classList.add('d-none');
        document.getElementById('mdeLoadMore').classList.toggle('d-none', state.page >= state.lastPage);
    });
    document.getElementById('mdeTableView').addEventListener('click', () => {
        state.view = 'table';
        document.getElementById('mdeCards').classList.add('d-none');
        document.getElementById('mdeTableWrap').classList.remove('d-none');
        document.getElementById('mdeLoadMore').classList.add('d-none');
    });
    document.getElementById('mdeLoadMore').addEventListener('click', () => { state.page += 1; load(false); });
    document.getElementById('mdeExportCsv').addEventListener('click', (event) => { event.preventDefault(); const url = new URL(root.dataset.exportCsv, window.location.origin); Object.entries(params()).forEach(([key, value]) => value && url.searchParams.set(key, value)); window.location.href = url.toString(); });
    document.getElementById('mdeExportExcel').addEventListener('click', (event) => { event.preventDefault(); const url = new URL(root.dataset.exportExcel, window.location.origin); Object.entries(params()).forEach(([key, value]) => value && url.searchParams.set(key, value)); window.location.href = url.toString(); });
    root.addEventListener('click', (event) => { const btn = event.target.closest('button[data-id]'); if (btn) details(btn.dataset.id); });

    load(true);
})();
