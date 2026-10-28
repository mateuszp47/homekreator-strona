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

// New upload previews. URLs are released when selection changes.
document.querySelectorAll('[data-image-preview]').forEach(input=>{
 let urls=[];
 input.addEventListener('change',()=>{
  urls.forEach(url=>URL.revokeObjectURL(url));urls=[];
  const list=document.getElementById(input.dataset.imagePreview);list.replaceChildren();input.setCustomValidity('');
  const files=[...input.files];const feedback=document.querySelector('[data-upload-feedback]');
  if(input.multiple&&files.length>30){input.setCustomValidity('Wybierz maksymalnie 30 zdjęć naraz.');input.reportValidity();return;}
  let total=0;
  files.forEach(file=>{
   total+=file.size;if(file.size>10*1024*1024)input.setCustomValidity('Każde zdjęcie może mieć maksymalnie 10 MB.');
   const figure=document.createElement('figure');const image=document.createElement('img');const caption=document.createElement('figcaption');
   const url=URL.createObjectURL(file);urls.push(url);image.src=url;image.alt='Podgląd: '+file.name;caption.textContent=file.name+' · '+(file.size/1048576).toFixed(1)+' MB';figure.append(image,caption);list.append(figure);
  });
  if(feedback)feedback.textContent=files.length?`Wybrano ${files.length} zdjęć (${(total/1048576).toFixed(1)} MB). Zostaną przetworzone przy zapisie.`:'';
  if(!input.checkValidity())input.reportValidity();
 });
});
const gallery=document.querySelector('[data-sortable-gallery]');let dragged;
gallery?.addEventListener('dragstart',event=>{dragged=event.target.closest('.gallery-sort-item');if(!dragged)return;dragged.classList.add('dragging');event.dataTransfer.effectAllowed='move';event.dataTransfer.setData('text/plain','gallery');});
gallery?.addEventListener('dragover',event=>{if(dragged)event.preventDefault();});
gallery?.addEventListener('drop',event=>{event.preventDefault();const target=event.target.closest('.gallery-sort-item');if(dragged&&target&&dragged!==target){const items=[...gallery.children];target.insertAdjacentElement(items.indexOf(dragged)<items.indexOf(target)?'afterend':'beforebegin',dragged);}});
gallery?.addEventListener('dragend',()=>{dragged?.classList.remove('dragging');dragged=null;});
gallery?.addEventListener('click',event=>{const button=event.target.closest('[data-move]');if(!button)return;const item=button.closest('.gallery-sort-item');const direction=Number(button.dataset.move);const sibling=direction<0?item.previousElementSibling:item.nextElementSibling;if(sibling)sibling.insertAdjacentElement(direction<0?'beforebegin':'afterend',item);button.focus();});
const faqList=document.querySelector('[data-faq-list]');
document.querySelector('[data-add-faq]')?.addEventListener('click',()=>{
 if(faqList.children.length>=30)return;
 const row=document.createElement('div');row.className='faq-editor-row';
 const index=Date.now();row.innerHTML=`<label>Pytanie<input name="faq[${index}][question]" maxlength="300" required></label><label>Odpowiedź<textarea name="faq[${index}][answer]" maxlength="3000" rows="3" required></textarea></label><button type="button" class="btn btn-outline" data-remove-faq>Usuń pytanie</button>`;faqList.append(row);row.querySelector('input').focus();
});
faqList?.addEventListener('click',event=>{if(event.target.closest('[data-remove-faq]')&&faqList.children.length>1)event.target.closest('.faq-editor-row').remove();});
