(function () {
    const root = document.getElementById('ldeResultsPage');
    if (!root) {
        return;
    }

    const state = {
        page: 1,
        lastPage: 1,
        view: 'cards',
    };

    const detailModal = new bootstrap.Modal(document.getElementById('ldeDetailModal'));

    function filters() {
        return {
            search: document.getElementById('ldeResultsSearch').value,
            country: document.getElementById('ldeFilterCountry').value,
            province: document.getElementById('ldeFilterProvince').value,
            city: document.getElementById('ldeFilterCity').value,
            category: document.getElementById('ldeFilterCategory').value,
            per_page: document.getElementById('ldePerPage').value,
            page: state.page,
        };
    }

    async function loadResults(reset = false) {
        if (reset) {
            state.page = 1;
        }

        const url = new URL(root.dataset.resultsEndpoint, window.location.origin);
        Object.entries(filters()).forEach(([key, value]) => {
            if (value) url.searchParams.set(key, value);
        });

        const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        const pager = payload.results;
        state.lastPage = pager.last_page;
        renderCards(pager.data, reset);
        renderTable(pager.data, reset);
        document.getElementById('ldeResultsCount').textContent = `${pager.total} results`;
        document.getElementById('ldeResultsPageInfo').textContent = `Page ${pager.current_page} / ${pager.last_page}`;
        document.getElementById('ldeLoadMore').classList.toggle('d-none', pager.current_page >= pager.last_page || state.view !== 'cards');
    }

    function renderCards(items, reset) {
        const wrap = document.getElementById('ldeResultsCards');
        if (reset) wrap.innerHTML = '';
        items.forEach((item) => {
            const card = document.createElement('article');
            card.className = 'lde-result-card';
            card.innerHTML = `
                <div class="lde-result-card-top">
                    <div>
                        <h3>${item.name}</h3>
                        <p>${item.address || '-'}</p>
                    </div>
                    <span class="badge text-bg-dark">${item.business_status || 'unknown'}</span>
                </div>
                <div class="lde-result-meta">
                    <span><i class="fas fa-star text-warning"></i> ${item.rating || '-'}</span>
                    <span><i class="fas fa-comments"></i> ${item.reviews_count || 0}</span>
                    <span><i class="fas fa-location-dot"></i> ${item.city || '-'}, ${item.country || '-'}</span>
                </div>
                <div class="lde-result-links">
                    <a href="${item.website || '#'}" target="_blank" ${item.website ? '' : 'class="disabled"'}>Website</a>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-id="${item.id}">Details</button>
                </div>`;
            wrap.appendChild(card);
        });
    }

    function renderTable(items, reset) {
        const tbody = document.querySelector('#ldeResultsTable tbody');
        if (reset) tbody.innerHTML = '';
        items.forEach((item) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.name}</td>
                <td>${item.address || '-'}</td>
                <td>${item.email || item.phone || '-'}</td>
                <td>${item.rating || '-'}</td>
                <td>${item.business_status || '-'}</td>
                <td><button type="button" class="btn btn-sm btn-outline-primary" data-id="${item.id}">Details</button></td>`;
            tbody.appendChild(row);
        });
    }

    async function loadDetail(id) {
        const response = await fetch(`${root.dataset.detailBase}/${id}`, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        const item = payload.data;
        document.getElementById('ldeDetailBody').innerHTML = `
            <div class="row g-4">
                <div class="col-lg-8">
                    <h3>${item.name}</h3>
                    <p>${item.address || '-'}</p>
                    <p><strong>Website:</strong> ${item.website ? `<a href="${item.website}" target="_blank">${item.website}</a>` : '-'}</p>
                    <p><strong>Email:</strong> ${item.email || '-'}</p>
                    <p><strong>Phone:</strong> ${item.phone || item.international_phone || '-'}</p>
                    <p><strong>Categories:</strong> ${(item.categories || []).join(', ')}</p>
                    <p><strong>Maps:</strong> ${item.google_maps_url ? `<a href="${item.google_maps_url}" target="_blank">Open map</a>` : '-'}</p>
                    <div class="mt-4"><strong>Reviews</strong>${(item.reviews || []).map((review) => `<div class="lde-review-card mt-2"><strong>${review.author_name || 'Review'}</strong><div class="small">Rating ${review.rating || '-'}</div><p class="mb-0 mt-2">${review.text || 'No review text.'}</p></div>`).join('') || '<p class="text-muted mt-2">No reviews.</p>'}</div>
                </div>
                <div class="col-lg-4">
                    <div class="lde-detail-side">
                        <div class="lde-mini-stat"><span>Country</span><strong>${item.country || '-'}</strong></div>
                        <div class="lde-mini-stat"><span>Province</span><strong>${item.province || '-'}</strong></div>
                        <div class="lde-mini-stat"><span>City</span><strong>${item.city || '-'}</strong></div>
                        <div class="lde-mini-stat"><span>Status</span><strong>${item.business_status || '-'}</strong></div>
                    </div>
                </div>
            </div>`;
        detailModal.show();
    }

    document.getElementById('ldeApplyFilters').addEventListener('click', () => loadResults(true));
    document.getElementById('ldeResetFilters').addEventListener('click', () => {
        ['ldeResultsSearch', 'ldeFilterCountry', 'ldeFilterProvince', 'ldeFilterCity'].forEach((id) => document.getElementById(id).value = '');
        document.getElementById('ldeFilterCategory').value = '';
        loadResults(true);
    });
    document.getElementById('ldeLoadMore').addEventListener('click', () => {
        state.page += 1;
        loadResults(false);
    });
    document.getElementById('ldeViewCards').addEventListener('click', () => {
        state.view = 'cards';
        document.getElementById('ldeResultsCards').classList.remove('d-none');
        document.getElementById('ldeResultsTableWrap').classList.add('d-none');
        document.getElementById('ldeLoadMore').classList.toggle('d-none', state.page >= state.lastPage);
    });
    document.getElementById('ldeViewTable').addEventListener('click', () => {
        state.view = 'table';
        document.getElementById('ldeResultsCards').classList.add('d-none');
        document.getElementById('ldeResultsTableWrap').classList.remove('d-none');
        document.getElementById('ldeLoadMore').classList.add('d-none');
    });

    document.getElementById('ldeExportCsv').addEventListener('click', (event) => {
        event.preventDefault();
        const url = new URL(root.dataset.exportCsv, window.location.origin);
        Object.entries(filters()).forEach(([key, value]) => value && url.searchParams.set(key, value));
        window.location.href = url.toString();
    });
    document.getElementById('ldeExportExcel').addEventListener('click', (event) => {
        event.preventDefault();
        const url = new URL(root.dataset.exportExcel, window.location.origin);
        Object.entries(filters()).forEach(([key, value]) => value && url.searchParams.set(key, value));
        window.location.href = url.toString();
    });

    root.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-id]');
        if (button) {
            loadDetail(button.dataset.id);
        }
    });

    loadResults(true);
})();
