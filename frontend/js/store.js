'use strict';
const menuButton = document.querySelector('.menu-button');
const storeNav = document.querySelector('#store-nav');
menuButton?.addEventListener('click', () => {
    const expanded = menuButton.getAttribute('aria-expanded') !== 'true';
    menuButton.setAttribute('aria-expanded', String(expanded));
    storeNav.classList.toggle('is-open', expanded);
});
storeNav?.addEventListener('click', (event) => {
    if (event.target.closest('a')) {
        menuButton?.setAttribute('aria-expanded', 'false');
        storeNav.classList.remove('is-open');
    }
});
