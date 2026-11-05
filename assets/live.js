'use strict';

const body = document.body;
const dots = document.querySelectorAll('[data-notification]');
const count = document.querySelector('#unread-count');
const list = document.querySelector('#message-list');
const url = new URL('index.php', location.href);
url.search = new URLSearchParams({
    live: 'messages',
    p: body.dataset.page,
    account: body.dataset.account,
    carpeta: new URLSearchParams(location.search).get('carpeta') || 'entrada',
    mensaje: new URLSearchParams(location.search).get('mensaje') || ''
});
let previousHtml;
let polling = false;
let timer;

async function refreshMessages() {
    if (polling) return;
    clearTimeout(timer);
    polling = true;
    try {
        const response = await fetch(url, {cache: 'no-store'});
        if (response.status === 401) {
            body.hidden = true;
            location.replace('index.php?p=login');
            return;
        }
        if (!response.ok) return;
        const data = await response.json();
        dots.forEach(dot => { dot.hidden = !data.dots[dot.dataset.notification]; });
        if (count) count.textContent = data.unread;
        if (list && previousHtml === undefined) previousHtml = list.innerHTML.trim();
        if (list && data.html.trim() !== previousHtml) {
            const openIds = new Set(Array.from(list.querySelectorAll('details[open]'),
                item => item.dataset.messageId));
            list.innerHTML = data.html;
            list.querySelectorAll('details').forEach(item => {
                item.open = openIds.has(item.dataset.messageId);
            });
            previousHtml = data.html.trim();
        }
    } catch (error) {
        // Retry on the next poll if the server or connection is unavailable.
    } finally {
        polling = false;
        timer = setTimeout(refreshMessages, 2000);
    }
}

window.addEventListener('pagehide', () => {
    body.classList.add("session-check");
});
window.addEventListener('pageshow', event => {
    if (event.persisted) location.reload();
});
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) refreshMessages();
});
refreshMessages();
