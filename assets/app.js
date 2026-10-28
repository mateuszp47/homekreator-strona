'use strict';
// Progressive enhancements: navigation and core content work without JavaScript.
const menuToggle = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#navigation');
function closeMenu() { navigation?.classList.remove('open'); menuToggle?.setAttribute('aria-expanded', 'false'); }
menuToggle?.addEventListener('click', () => {
  const open = menuToggle.getAttribute('aria-expanded') !== 'true';
  menuToggle.setAttribute('aria-expanded', String(open)); navigation.classList.toggle('open', open);
});
document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
document.addEventListener('click', event => { if (!event.target.closest('.header')) closeMenu(); });
navigation?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
const filters = [...document.querySelectorAll('[data-filter]')];
const items = [...document.querySelectorAll('[data-category]')];
const search = document.querySelector('#product-search');
let category = new URLSearchParams(location.search).get('kategoria') || 'wszystkie';
if (!filters.some(button => button.dataset.filter === category)) category = 'wszystkie';
function updateCatalog() {
  const term = (search?.value || '').trim().toLocaleLowerCase('pl');
  let visible = 0;
  items.forEach(item => {
    const match = (category === 'wszystkie' || item.dataset.category === category) && (!term || (item.dataset.name || '').includes(term));
    item.hidden = !match; if (match) visible++;
  });
  filters.forEach(button => {
    const active = button.dataset.filter === category;
    button.classList.toggle('active', active); button.setAttribute('aria-pressed', String(active));
  });
  const count = document.querySelector('#result-count');
  if (count) count.textContent = `Liczba produktów: ${visible}`;
  const empty = document.querySelector('#empty-results'); if (empty) empty.hidden = visible !== 0;
}
filters.forEach(button => button.addEventListener('click', () => {
  category = button.dataset.filter;
  const url = new URL(location.href);
  if (category === 'wszystkie') url.searchParams.delete('kategoria'); else url.searchParams.set('kategoria', category);
  history.replaceState(null, '', url); updateCatalog();
}));
search?.addEventListener('input', updateCatalog);
if (filters.length) updateCatalog();
const dialog = document.querySelector('#lightbox');
let focusedBeforeDialog;
document.querySelectorAll('[data-lightbox]').forEach(button => button.addEventListener('click', () => {
  focusedBeforeDialog = button;
  dialog.querySelector('img').src = button.dataset.lightbox;
  dialog.querySelector('img').alt = button.dataset.caption;
  dialog.querySelector('p').textContent = button.dataset.caption;
  dialog.showModal(); document.body.classList.add('modal-open');
}));
function closeLightbox() { dialog?.close(); }
dialog?.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
dialog?.addEventListener('click', event => { if (event.target === dialog) closeLightbox(); });
dialog?.addEventListener('close', () => { document.body.classList.remove('modal-open'); focusedBeforeDialog?.focus(); });
const mainPhoto = document.querySelector('.product-main-photo');
document.querySelectorAll('[data-photo]').forEach(button => button.addEventListener('click', () => {
  mainPhoto.querySelector('img').src = button.dataset.photo;
  mainPhoto.dataset.lightbox = button.dataset.photo;
  document.querySelectorAll('[data-photo]').forEach(thumb => thumb.setAttribute('aria-pressed', String(thumb === button)));
}));
const form = document.querySelector('#inquiry-form');
const model = document.querySelector('#inquiry-model');
if (model) {
  const requested = new URLSearchParams(location.search).get('model');
  if ([...model.options].some(option => option.value === requested)) model.value = requested;
}
form?.addEventListener('submit', event => {
  event.preventDefault();
  const data = new FormData(form);
  const lines = ['Dzień dobry, proszę o ofertę Home Kreator.', `Model: ${model.selectedOptions[0].textContent}`, `Wymiary: ${String(data.get('dimensions')).trim() || 'do ustalenia'}`, `Miejscowość dostawy: ${String(data.get('city')).trim()}`, `Opis: ${String(data.get('message')).trim()}`];
  document.querySelector('#inquiry-text').value = lines.join('\n\n');
  const result = document.querySelector('#inquiry-result'); result.hidden = false;
  result.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'center' });
  document.querySelector('#copy-status').textContent = 'Zapytanie przygotowane. Nie zostało wysłane.';
});
document.querySelector('#copy-inquiry')?.addEventListener('click', async () => {
  const text = document.querySelector('#inquiry-text');
  try {
    await navigator.clipboard.writeText(text.value);
    document.querySelector('#copy-status').textContent = 'Skopiowano treść zapytania.';
  } catch {
    text.focus(); text.select(); document.querySelector('#copy-status').textContent = 'Zaznaczono treść. Skopiuj ją skrótem Ctrl+C lub ⌘C.';
  }
});
