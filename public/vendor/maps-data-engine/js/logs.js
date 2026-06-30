(function () {
    const root = document.getElementById('mdeLogsPage');
    if (!root) return;

    let timer = null;
    let sessionId = null;

    async function loadLogs() {
        if (!sessionId) return;
        const response = await fetch(`${root.dataset.logsBase}/${sessionId}/logs`, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        const body = document.getElementById('mdeLogsBody');
        body.innerHTML = '';
        (payload.logs.data || []).forEach((log) => {
            const row = document.createElement('tr');
            row.innerHTML = `<td>${log.created_at || ''}</td><td>${log.level}</td><td>${log.event || '-'}</td><td>${log.message}</td>`;
            body.appendChild(row);
        });
        document.getElementById('mdeLogsEmpty').classList.add('d-none');
        document.getElementById('mdeLogsWrap').classList.remove('d-none');
    }

    document.querySelectorAll('.mde-session-item').forEach((item) => {
        item.addEventListener('click', () => {
            sessionId = item.dataset.sessionId;
            document.querySelectorAll('.mde-session-item').forEach((node) => node.classList.remove('active'));
            item.classList.add('active');
            clearInterval(timer);
            loadLogs();
            timer = setInterval(loadLogs, 5000);
        });
    });
})();
