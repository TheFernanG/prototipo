'use strict';

const body = document.body;
const status = document.querySelector('#connection-status');
const url = new URL(location.href);
url.hash = '';
url.searchParams.set('live', 'messages');
url.searchParams.set('account', body.dataset.account);
let polling = false;
let timer;
let controller;
const snapshots = new Map();
document.querySelectorAll('[data-live]').forEach(region => {
    snapshots.set(region.dataset.live, region.innerHTML);
});

function showStatus(message) {
    if (status) status.textContent = message;
}

function updateRegion(current, incoming) {
    const key = current.dataset.live;
    const html = incoming.innerHTML;
    if (snapshots.get(key) === html) return;
    // Keep controls usable while a person is interacting with a record.
    if (current.contains(document.activeElement)) return;
    const selection = window.getSelection();
    if (selection && !selection.isCollapsed && current.contains(selection.anchorNode)) return;

    const open = new Map(Array.from(current.querySelectorAll('details[data-message-id]'),
        item => [item.dataset.messageId, item.open]));
    const scroll = Array.from(current.querySelectorAll('.table-wrap'),
        item => [item.scrollLeft, item.scrollTop]);
    current.innerHTML = html;
    current.querySelectorAll('details[data-message-id]').forEach(item => {
        if (open.has(item.dataset.messageId)) item.open = open.get(item.dataset.messageId);
    });
    current.querySelectorAll('.table-wrap').forEach((item, index) => {
        if (scroll[index]) [item.scrollLeft, item.scrollTop] = scroll[index];
    });
    snapshots.set(key, html);
}

async function refresh() {
    clearTimeout(timer);
    if (polling || document.hidden) return;
    if (!navigator.onLine) {
        showStatus('Sin conexión. Se actualizará al reconectar.');
        return;
    }
    polling = true;
    controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 10000);
    try {
        const response = await fetch(url, {
            cache: 'no-store',
            credentials: 'same-origin',
            signal: controller.signal
        });
        if (response.status === 401) {
            location.replace('index.php?p=login');
            return;
        }
        if (!response.ok) throw new Error('Actualización pendiente');
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        if (page.body.dataset.account !== body.dataset.account) {
            location.replace('index.php?p=login');
            return;
        }
        document.querySelectorAll('[data-notification]').forEach(dot => {
            const incoming = page.querySelector('[data-notification="' + dot.dataset.notification + '"]');
            if (incoming) dot.hidden = incoming.hidden;
        });
        document.querySelectorAll('[data-live]').forEach(region => {
            const incoming = page.querySelector('[data-live="' + region.dataset.live + '"]');
            if (incoming) updateRegion(region, incoming);
        });
        showStatus('Actualización automática activa');
    } catch (error) {
        if (!document.hidden) showStatus('Reconectando…');
    } finally {
        clearTimeout(timeout);
        polling = false;
        controller = null;
        if (!document.hidden) timer = setTimeout(refresh, 2000);
    }
}

document.addEventListener('visibilitychange', () => {
    clearTimeout(timer);
    if (document.hidden) {
        if (controller) controller.abort();
    } else {
        refresh();
    }
});
window.addEventListener('online', refresh);
window.addEventListener('offline', () => {
    showStatus('Sin conexión. Se actualizará al reconectar.');
});
window.addEventListener('pagehide', () => {
    clearTimeout(timer);
    if (controller) controller.abort();
    body.classList.add('session-check');
});
window.addEventListener('pageshow', event => {
    if (event.persisted) location.reload();
});
refresh();
