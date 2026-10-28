const toggle = document.querySelector('.menu-toggle');
const nav = document.querySelector('#navigation');
const dropdownButton = document.querySelector('.offer-nav-title button');
const offerNav = document.querySelector('.offer-nav');
function closeMenus() {
 toggle?.setAttribute('aria-expanded','false'); nav?.classList.remove('open');
 dropdownButton?.setAttribute('aria-expanded','false'); offerNav?.classList.remove('open');
}
toggle?.addEventListener('click',()=>{const open=toggle.getAttribute('aria-expanded')!=='true';toggle.setAttribute('aria-expanded',String(open));nav.classList.toggle('open',open);});
dropdownButton?.addEventListener('click',()=>{const open=dropdownButton.getAttribute('aria-expanded')!=='true';dropdownButton.setAttribute('aria-expanded',String(open));offerNav.classList.toggle('open',open);});
document.addEventListener('click',event=>{if(!event.target.closest('.site-header')) closeMenus();});
document.addEventListener('keydown',event=>{if(event.key==='Escape') closeMenus();});
const dialog=document.querySelector('#lightbox');let previousFocus;
document.querySelectorAll('[data-lightbox]').forEach(button=>button.addEventListener('click',()=>{
 previousFocus=button;dialog.querySelector('img').src=button.dataset.lightbox;dialog.querySelector('img').alt=button.dataset.caption;dialog.querySelector('p').textContent=button.dataset.caption;dialog.showModal();document.body.classList.add('modal-open');
}));
dialog?.querySelector('[data-close-lightbox]').addEventListener('click',()=>dialog.close());
dialog?.addEventListener('click',event=>{if(event.target===dialog) dialog.close();});
dialog?.addEventListener('close',()=>{document.body.classList.remove('modal-open');previousFocus?.focus();});
const photo=document.querySelector('.main-photo');
document.querySelectorAll('[data-photo]').forEach(button=>button.addEventListener('click',()=>{
 photo.querySelector('img').src=button.dataset.photo;photo.dataset.lightbox=button.dataset.photo;
 document.querySelectorAll('[data-photo]').forEach(item=>item.setAttribute('aria-pressed',String(item===button)));
}));
// Prevent accidental double submits after the browser has validated the form.
document.querySelector('[data-contact-submit]')?.form?.addEventListener('submit',event=>{
 const button=event.target.querySelector('[data-contact-submit]');button.disabled=true;button.textContent='Przesyłanie zapytania…';
});
window.addEventListener('pageshow',()=>{const button=document.querySelector('[data-contact-submit]');if(button){button.disabled=false;button.innerHTML='Wyślij zapytanie <span aria-hidden="true">→</span>';}});

// Keep the chevron and aria-expanded in sync for pointer and keyboard users.
function setOfferOpen(open){dropdownButton?.setAttribute('aria-expanded',String(open));offerNav?.classList.toggle('open',open);}
offerNav?.addEventListener('pointerenter',()=>{if(matchMedia('(hover: hover) and (min-width: 901px)').matches)setOfferOpen(true);});
offerNav?.addEventListener('pointerleave',()=>{if(matchMedia('(hover: hover) and (min-width: 901px)').matches&&!offerNav.matches(':focus-within'))setOfferOpen(false);});
offerNav?.addEventListener('focusout',event=>{if(!offerNav.contains(event.relatedTarget)&&!offerNav.matches(':hover'))setOfferOpen(false);});
