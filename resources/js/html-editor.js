const allowed=new Set(['p','div','span','br','h2','h3','h4','strong','b','em','i','u','s','del','ul','ol','li','blockquote','pre','code','hr','a','table','thead','tbody','tr','th','td']);
const dropped=new Set(['script','style','iframe','object','embed','svg','math','form','input','button','textarea','select','option','template','link','meta','base']);
const safeUrl=value=>/^(https?:\/\/|mailto:|tel:|\/(?!\/)|#)/i.test(value)&&!/[\u0000-\u0020\u007f]/.test(value);
function safeHtml(value){
 const fragment=document.createDocumentFragment();
 if(!/<\/?[a-z][^>]*>/i.test(value)){
  const paragraph=document.createElement('p');value.split(/\r\n|\r|\n/).forEach((line,index)=>{if(index)paragraph.append(document.createElement('br'));paragraph.append(document.createTextNode(line));});fragment.append(paragraph);
 }else{
  const parsed=new DOMParser().parseFromString(value,'text/html');
  function copy(parent,target){for(const node of parent.childNodes){
   if(node.nodeType===Node.TEXT_NODE){target.append(document.createTextNode(node.textContent));continue;}
   if(node.nodeType!==Node.ELEMENT_NODE)continue;
   const tag=node.tagName.toLowerCase();if(dropped.has(tag))continue;
   if(!allowed.has(tag)){copy(node,target);continue;}
   const element=document.createElement(tag);
   if(tag==='a'&&safeUrl(node.getAttribute('href')?.trim()||''))element.setAttribute('href',node.getAttribute('href').trim());
   copy(node,element);target.append(element);
  }}copy(parsed.body,fragment);
 }
 const result=document.createElement('div');result.append(fragment);return result.innerHTML;
}
document.querySelectorAll('[data-html-editor]').forEach(root=>{
 const source=root.querySelector('[data-editor-textarea]'),visual=root.querySelector('[data-editor-visual]'),toolbar=root.querySelector('[data-editor-toolbar]'),mode=root.querySelector('[data-editor-source]'),error=root.querySelector('[data-editor-error]');
 const linkPanel=root.querySelector('[data-editor-link-panel]'),linkUrl=root.querySelector('[data-editor-link-url]');
 let code=false,savedRange;
 const closeLink=()=>{linkPanel.hidden=true;linkUrl.disabled=true;};
 visual.innerHTML=safeHtml(source.value);source.hidden=true;source.required=false;toolbar.hidden=false;visual.hidden=false;
 const sync=()=>{if(!code)source.value=visual.innerHTML;error.hidden=true;};
 visual.addEventListener('input',sync);
 toolbar.addEventListener('mousedown',event=>{if(event.target.closest('button'))event.preventDefault();});
 toolbar.addEventListener('click',event=>{
  const button=event.target.closest('[data-editor-command]');if(!button||code)return;
  visual.focus();document.execCommand(button.dataset.editorCommand,false,button.dataset.editorValue||null);sync();
 });
 visual.addEventListener('paste',event=>{
  event.preventDefault();const html=event.clipboardData.getData('text/html'),text=event.clipboardData.getData('text/plain');
  document.execCommand('insertHTML',false,safeHtml(html||text));sync();
 });
 mode.addEventListener('click',()=>{
  if(code){visual.innerHTML=safeHtml(source.value);source.value=visual.innerHTML;}
  else source.value=visual.innerHTML;
  code=!code;source.hidden=!code;visual.hidden=code;mode.setAttribute('aria-pressed',String(code));closeLink();
  toolbar.querySelectorAll('button').forEach(button=>{if(button!==mode)button.disabled=code;});
  (code?source:visual).focus();
 });
 root.querySelector('[data-editor-link]').addEventListener('click',()=>{
  const selection=window.getSelection();savedRange=selection.rangeCount?selection.getRangeAt(0).cloneRange():null;
  linkPanel.hidden=false;linkUrl.disabled=false;linkUrl.value='https://';linkUrl.focus();
 });
 root.querySelector('[data-editor-link-cancel]').addEventListener('click',()=>{closeLink();visual.focus();});
 root.querySelector('[data-editor-link-apply]').addEventListener('click',()=>{
  const url=linkUrl.value.trim();if(!safeUrl(url)){error.textContent='Podaj poprawny adres linku, np. https://homekreator.com.';error.hidden=false;linkUrl.focus();return;}
  visual.focus();if(savedRange&&visual.contains(savedRange.commonAncestorContainer)){const selection=window.getSelection();selection.removeAllRanges();selection.addRange(savedRange);}
  const selection=window.getSelection();
  if(selection.isCollapsed){const anchor=document.createElement('a');anchor.href=url;anchor.textContent=url;document.execCommand('insertHTML',false,anchor.outerHTML);}
  else document.execCommand('createLink',false,url);
  closeLink();sync();
 });
 source.form.addEventListener('submit',event=>{
  sync();const text=new DOMParser().parseFromString(safeHtml(source.value),'text/html').body.textContent.replace(/[\s\u00a0\u200b]+/g,'').trim();
  if(!text||source.value.length>15000){event.preventDefault();error.textContent=!text?'Uzupełnij opis produktu lub realizacji.':'Opis może zawierać maksymalnie 15 000 znaków kodu HTML.';error.hidden=false;(code?source:visual).focus();}
 });
});
