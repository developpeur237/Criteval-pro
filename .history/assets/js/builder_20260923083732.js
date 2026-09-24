(function () {
  const kinds = ['text','email','number','date','textarea','select','checkbox','radio','file','heading','paragraph','divider','section'];
  const labels = {text:'Texte',email:'E-mail',number:'Nombre',date:'Date',textarea:'Zone de texte',select:'Liste',checkbox:'Cases à cocher',radio:'Choix unique',file:'Fichier',heading:'Titre',paragraph:'Paragraphe',divider:'Séparateur',section:'Section'};
  const slug = value => String(value || 'champ').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'') || 'champ';
  const fresh = (type, position) => { const id = crypto.randomUUID ? crypto.randomUUID() : 'field-'+Date.now()+Math.random(); return Object.assign({id,type,label:labels[type]||'Nouveau champ',name:slug((labels[type]||'champ')+'_'+String(id).slice(-6)),placeholder:'',options:'Option 1\nOption 2',required:false,span:6,rowSpan:1,scale:100,background:'#ffffff',color:'#1e293b',radius:10}, position || {}); };
  const normalize = (item, index) => Object.assign(fresh(item.type || 'text', {id:item.id || 'field-'+index}), item, {name:slug(item.name || item.label || 'champ_'+index),options:item.options || 'Option 1\nOption 2',span:Math.max(1,Math.min(12,+item.span||6)),rowSpan:Math.max(1,Math.min(6,+item.rowSpan||1))});
  window.CritevalBuilder = { init(options) {
    const canvas = document.getElementById(options.canvas);
    const input = document.getElementById(options.input);
    const palette = document.getElementById('element-palette');
    const editor = document.getElementById('property-editor');
    const empty = document.getElementById('property-empty');
    if (!canvas || !input) return;
    if (canvas.dataset.builderReady === '1') return;
    canvas.dataset.builderReady = '1';
    let items=[], selected=null, history=[], future=[], grid=true;
    try { items = Array.isArray(JSON.parse(input.value)) ? JSON.parse(input.value).map(normalize) : []; } catch (_) { items=[]; }
    const snapshot=()=>{history.push(JSON.stringify(items)); if(history.length>50)history.shift(); future=[]; input.value=JSON.stringify(items);};
    const selectedItem=()=>items.find(x=>x.id===selected);
    const typeSelector = document.querySelector('[data-prop="type"]');
    if (typeSelector) { const optionHtml=kinds.map(k=>`<option value="${k}">${labels[k]}</option>`).join(''); typeSelector.innerHTML=optionHtml; }
    const render=()=>{ canvas.innerHTML=''; canvas.classList.toggle('grid-on',grid); items.forEach((field,index)=>{ const card=document.createElement('article');card.className='builder-element'+(field.id===selected?' selected':'');card.draggable=true;card.dataset.id=field.id;card.style.gridColumn=`span ${field.span}`;card.style.gridRow=`span ${field.rowSpan}`;card.style.setProperty('--builder-bg',field.background);card.style.setProperty('--builder-color',field.color);card.style.setProperty('--builder-radius',field.radius+'px');card.style.setProperty('--builder-scale',field.scale/100); card.innerHTML=`<div class="builder-element-head"><span class="drag-handle" title="Déplacer">⠿</span><small>${labels[field.type]}</small><button type="button" data-action="remove" aria-label="Supprimer">×</button></div><label>${escapeHtml(field.label)}${field.required?' <em>*</em>':''}</label>${preview(field)}<span class="resize-handle" title="Redimensionner"></span>`; canvas.append(card); }); input.value=JSON.stringify(items); syncProperties(); };
    const escapeHtml=s=>String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const preview=f=> { const p=escapeHtml(f.placeholder); const options=String(f.options||'').split(/\r?\n/).map(escapeHtml).filter(Boolean); if(f.type==='textarea')return `<textarea disabled placeholder="${p}"></textarea>`; if(f.type==='select')return `<select disabled>${(options.length?options:[p||'Choisir…']).map(o=>`<option>${o}</option>`).join('')}</select>`; if(f.type==='checkbox'||f.type==='radio')return `<span class="choice-preview">${(options.length?options:['Option 1','Option 2']).map(o=>`◯ ${o}`).join(' &nbsp; ')}</span>`; if(f.type==='heading')return `<h3>${escapeHtml(f.label)}</h3>`; if(f.type==='paragraph')return `<p>${p||'Texte descriptif'}</p>`; if(f.type==='divider')return '<hr>'; if(f.type==='section')return `<div class="section-preview">${p||'Zone de section flexible'}</div>`; return `<input disabled type="${f.type==='file'?'file':f.type}" placeholder="${p}">`; };
    const syncProperties=()=>{ const f=selectedItem(); if (editor) editor.hidden=!f; if (empty) empty.hidden=!!f; if(!f)return; editor.querySelectorAll('[data-prop]').forEach(el=>{const prop=el.dataset.prop;el.type==='checkbox'?el.checked=!!f[prop]:el.value=f[prop] ?? '';}); editor.querySelectorAll('[data-out]').forEach(o=>o.value=f[o.dataset.out]||''); };
    if (palette) {
      palette.innerHTML = kinds.map(k => `<button type="button" draggable="true" data-kind="${k}">+ ${labels[k]}</button>`).join('');
      palette.addEventListener('click',e=>{const type=e.target.dataset.kind;if(!type)return;snapshot();const item=fresh(type);items.push(item);selected=item.id;render();});
      palette.addEventListener('dragstart',e=>e.dataTransfer.setData('type',e.target.dataset.kind||''));
    }
    canvas.addEventListener('dragover',e=>e.preventDefault()); canvas.addEventListener('drop',e=>{e.preventDefault(); const type=e.dataTransfer.getData('type'), moving=e.dataTransfer.getData('move'); snapshot();if(type){const f=fresh(type);items.push(f);selected=f.id;}else if(moving){const i=items.findIndex(x=>x.id===moving), target=e.target.closest('.builder-element');if(i>=0&&target){const [f]=items.splice(i,1);items.splice(Math.max(0,items.findIndex(x=>x.id===target.dataset.id)),0,f);}}render();});
    canvas.addEventListener('dragstart',e=>{const card=e.target.closest('.builder-element');if(card)e.dataTransfer.setData('move',card.dataset.id)});
    canvas.addEventListener('click',e=>{const card=e.target.closest('.builder-element');if(!card)return;if(e.target.dataset.action==='remove'){snapshot();items=items.filter(x=>x.id!==card.dataset.id);if(selected===card.dataset.id)selected=null;}else selected=card.dataset.id;render();});
    if (editor) {
      editor.addEventListener('input',e=>{const f=selectedItem(), prop=e.target.dataset.prop;if(!f||!prop)return;f[prop]=e.target.type==='checkbox'?e.target.checked:(['span','rowSpan','scale','radius'].includes(prop)?+e.target.value:(prop==='name'?slug(e.target.value):e.target.value));input.value=JSON.stringify(items);if(['span','rowSpan','scale','radius','background','color','type','options','name'].includes(prop))render();});
      editor.addEventListener('change',e=>{snapshot();render();});
    }
    const toggleGrid = document.getElementById('toggle-grid');
    if (toggleGrid) toggleGrid.onclick=()=>{grid=!grid;toggleGrid.textContent='Grille : '+(grid?'active':'masquée');render();};
    const deleteElement = document.getElementById('delete-element');
    if (deleteElement) deleteElement.onclick=()=>{if(!selected)return;snapshot();items=items.filter(x=>x.id!==selected);selected=null;render();};
    const duplicateElement = document.getElementById('duplicate-element');
    if (duplicateElement) duplicateElement.onclick=()=>{const f=selectedItem();if(!f)return;snapshot();const copy=normalize(Object.assign({},f,{id:null,label:f.label+' (copie)'}),items.length);items.push(copy);selected=copy.id;render();};
    const splitElement = document.getElementById('split-element');
    if (splitElement) splitElement.onclick=()=>{const f=selectedItem();if(!f||f.span<2)return;snapshot();f.span=Math.ceil(f.span/2);const twin=normalize(Object.assign({},f,{id:null,label:f.label+' B'}),items.length);items.splice(items.indexOf(f)+1,0,twin);selected=twin.id;render();};
    canvas.addEventListener('pointerdown',e=>{const handle=e.target.closest('.resize-handle'),card=e.target.closest('.builder-element');if(!handle||!card)return;const f=items.find(x=>x.id===card.dataset.id), startX=e.clientX,startY=e.clientY,startSpan=f.span,startRows=f.rowSpan;snapshot();const move=event=>{f.span=Math.max(1,Math.min(12,startSpan+Math.round((event.clientX-startX)/55)));f.rowSpan=Math.max(1,Math.min(6,startRows+Math.round((event.clientY-startY)/70)));input.value=JSON.stringify(items);card.style.gridColumn=`span ${f.span}`;card.style.gridRow=`span ${f.rowSpan}`;syncProperties();};const up=()=>{window.removeEventListener('pointermove',move);window.removeEventListener('pointerup',up);render();};window.addEventListener('pointermove',move);window.addEventListener('pointerup',up);});
    const undoButton = document.getElementById('undo-builder');
    if (undoButton) undoButton.onclick=()=>{if(!history.length)return;future.push(JSON.stringify(items));items=JSON.parse(history.pop()).map(normalize);selected=null;render();};
    const redoButton = document.getElementById('redo-builder');
    if (redoButton) redoButton.onclick=()=>{if(!future.length)return;history.push(JSON.stringify(items));items=JSON.parse(future.pop()).map(normalize);selected=null;render();};
    render();
  }};
})();
