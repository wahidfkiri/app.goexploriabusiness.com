(function () {
    const root = document.getElementById('ldeLogsPage');
    if (!root) {
        return;
    }

    let currentSessionId = null;
    let timer = null;

    async function loadLogs() {
        if (!currentSessionId) return;

        const response = await fetch(`${root.dataset.logsBase}/${currentSessionId}/logs`, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        const logs = payload.logs.data || [];
        const tbody = document.getElementById('ldeLogsTableBody');
        tbody.innerHTML = '';
        logs.forEach((log) => {
            const row = document.createElement('tr');
            row.innerHTML = `<td>${log.created_at || ''}</td><td>${log.level}</td><td>${log.event || '-'}</td><td>${log.message}</td>`;
            tbody.appendChild(row);
        });
        document.getElementById('ldeLogsEmpty').classList.add('d-none');
        document.getElementById('ldeLogsTableWrap').classList.remove('d-none');
    }

    document.querySelectorAll('.lde-session-item').forEach((item) => {
        item.addEventListener('click', () => {
            currentSessionId = item.dataset.sessionId;
            document.querySelectorAll('.lde-session-item').forEach((node) => node.classList.remove('active'));
            item.classList.add('active');
            clearInterval(timer);
            loadLogs();
            timer = setInterval(loadLogs, 5000);
        });
    });
})();
