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

document.querySelectorAll('[data-open-store-dialog]').forEach((button) => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.openStoreDialog);
        if (!dialog) return;
        storeNav?.classList.remove('is-open');
        menuButton?.setAttribute('aria-expanded', 'false');
        document.querySelectorAll('.store-account[open]').forEach((account) => account.removeAttribute('open'));
        dialog.showModal();
        document.body.classList.add('store-dialog-open');
    });
});

document.querySelectorAll('[data-close-store-dialog]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog').close());
});

document.querySelectorAll('.store-search-dialog, .store-cart-dialog').forEach((dialog) => {
    dialog.addEventListener('close', () => document.body.classList.remove('store-dialog-open'));
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const bounds = dialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) {
            dialog.close();
        }
    });
});

document.addEventListener('click', (event) => {
    document.querySelectorAll('.store-account[open]').forEach((account) => {
        if (!account.contains(event.target)) account.removeAttribute('open');
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    storeNav?.classList.remove('is-open');
    menuButton?.setAttribute('aria-expanded', 'false');
    document.querySelectorAll('.store-account[open]').forEach((account) => account.removeAttribute('open'));
});

// Category search stays on the landing page now that browsing is consolidated.
const categoryCards = Array.from(document.querySelectorAll('.hardware-category'));
const categoryFeedback = document.getElementById('landing-category-feedback');

function filterLandingCategories(query) {
    if (!categoryCards.length || !categoryFeedback) return;
    const normalized = query.trim().toLocaleLowerCase();
    let matches = 0;
    categoryCards.forEach((card) => {
        card.hidden = normalized !== '' && !card.textContent.toLocaleLowerCase().includes(normalized);
        if (!card.hidden) matches++;
    });
    categoryFeedback.hidden = normalized === '';
    categoryFeedback.textContent = matches
        ? `${matches} ${matches === 1 ? 'category' : 'categories'} matching “${query.trim()}”. Choose All categories to reset.`
        : `No categories match “${query.trim()}”. Choose All categories to see everything.`;
}

document.getElementById('landing-category-search')?.addEventListener('submit', (event) => {
    if (!categoryCards.length) return;
    event.preventDefault();
    const query = document.getElementById('header-search').value;
    filterLandingCategories(query);
    event.currentTarget.closest('dialog').close();
    const url = new URL(window.location.href);
    url.searchParams.set('q', query);
    url.hash = 'categories';
    window.history.replaceState({}, '', url);
    document.getElementById('categories').scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
});

document.querySelectorAll('[data-clear-category-search]').forEach((link) => {
    link.addEventListener('click', () => {
        filterLandingCategories('');
        const url = new URL(window.location.href);
        url.searchParams.delete('q');
        window.history.replaceState({}, '', url);
    });
});

const initialCategoryQuery = new URLSearchParams(window.location.search).get('q');
if (initialCategoryQuery) filterLandingCategories(initialCategoryQuery);
