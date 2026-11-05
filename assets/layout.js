'use strict';

const sidebar = document.querySelector('.sidebar');
const menuToggle = document.querySelector('#menu-toggle');

if (sidebar && menuToggle) {
    const mobile = window.matchMedia('(max-width: 860px)');
    const navigation = document.querySelector('#school-navigation');
    const menuLabel = menuToggle.querySelector('[data-menu-label]');
    const indicator = document.querySelector('#mobile-notification-dot');

    function updateIndicator() {
        const unread = Boolean(navigation.querySelector('[data-notification]:not([hidden])'));
        indicator.hidden = !unread;
        const action = sidebar.hasAttribute('data-menu-open') ? 'Cerrar menú' : 'Abrir menú';
        menuToggle.setAttribute('aria-label', action + (unread ? '. Hay novedades' : ''));
    }

    function setMenu(open) {
        sidebar.toggleAttribute('data-menu-open', open);
        menuToggle.setAttribute('aria-expanded', String(open));
        menuLabel.textContent = open ? 'Cerrar' : 'Menú';
        updateIndicator();
    }

    sidebar.setAttribute('data-collapsible', '');
    menuToggle.addEventListener('click', () => {
        setMenu(!sidebar.hasAttribute('data-menu-open'));
    });
    sidebar.addEventListener('keydown', event => {
        if (event.key === 'Escape' && mobile.matches && sidebar.hasAttribute('data-menu-open')) {
            setMenu(false);
            menuToggle.focus();
        }
    });
    mobile.addEventListener('change', () => setMenu(false));
    new MutationObserver(updateIndicator).observe(navigation, {
        attributes: true,
        attributeFilter: ['hidden'],
        subtree: true
    });
    setMenu(false);
}
