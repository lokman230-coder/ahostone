/**
 * Ahost One Builder Pro v1.0 - Sürükle-Bırak & Boyutlandırma
 * Site, Admin ve Müşteri Paneli için Profesyonel Builder
 */
(function(){
const $=(s,p=document)=>p.querySelector(s), $$=(s,p=document)=>Array.from(p.querySelectorAll(s));
const state={target:'site',template:'home',rows:[],selected:null,history:[],future:[],dragSrc:null,dragRowSrc:null};
const defaultBlocks={hero:{title:'Premium Hero',text:'Domain, hosting, marketplace ve AI tek panelde.',button:'Hemen Başla'},product:{title:'Ürün/Paket',text:'Hosting, VPS, web tasarım veya lisans ürünü.',price:'₺149/ay'},domain:{title:'Domain Search Center',text:'Registrar fiyat + komisyon ile canlı satış fiyatı.'},kpi:{title:'Dashboard KPI',text:'MRR, ARR, müşteri, domain, ticket SLA.'},renewal:{title:'Yenileme Kartı',text:'Hosting/domain ödeme tarihi ve kalan gün.'},invoice:{title:'Fatura Kartı',text:'Son faturalar, ödeme durumu ve tahsilat.'},ticket:{title:'Ticket Kartı',text:'Açık destek kayıtları ve SLA.'},text:{title:'Metin Bloğu',text:'Tanıtım, açıklama veya içerik alanı.'},pricing:{title:'Fiyat Tablosu',text:'Başlangıç / Pro / Enterprise karşılaştırması.',price:'₺299/ay'},testimonial:{title:'Müşteri Yorumu',text:'Ahost One operasyonu tek panele topladı.'},faq:{title:'SSS',text:'Sık sorulan soru ve cevap bloğu.'},chart:{title:'Grafik',text:'Gelir, sipariş, kaynak kullanımı veya SLA grafiği.'},form:{title:'Form',text:'Teklif, destek, başvuru veya iletişim formu.'},media:{title:'Medya',text:'Görsel, video, galeri veya slider.'}};
function uid(){return 'bp_'+Math.random().toString(36).slice(2,9)}
function snap(){state.history.push(JSON.stringify(state.rows)); if(state.history.length>40)state.history.shift(); state.future=[];}

// ==================== INIT ====================
function init(){
  let raw=$('#bp_json')?.value||'[]'; 
  try{state.rows=JSON.parse(raw)||[]}catch(e){state.rows=[]} 
  if(!state.rows.length) addRow(2,false); 
  render(); bind();
  initDragDrop();
  initColResize();
  initRowDragDrop();
  initWidgetResize();
}

// ==================== BIND EVENTS ====================
function bind(){
  $$('.bp-target').forEach(b=>b.onclick=()=>{
    state.target=b.dataset.target; 
    $$('.bp-target').forEach(x=>x.classList.toggle('active',x===b)); 
    $('#bp_target').value=state.target;
  });
  $$('.bp-block-item').forEach(b=>b.onclick=()=>addWidgetToFirst(b.dataset.type));
  $('#bpAddRow')?.addEventListener('click',()=>addRow(parseInt($('#bpCols').value||'1'),true));
  $('#bpUndo')?.addEventListener('click',()=>{if(!state.history.length)return; state.future.push(JSON.stringify(state.rows)); state.rows=JSON.parse(state.history.pop()); render(); initDragDrop(); initColResize(); initRowDragDrop(); initWidgetResize();});
  $('#bpRedo')?.addEventListener('click',()=>{if(!state.future.length)return; state.history.push(JSON.stringify(state.rows)); state.rows=JSON.parse(state.future.pop()); render(); initDragDrop(); initColResize(); initRowDragDrop(); initWidgetResize();});
  $('#bpSave')?.addEventListener('click',()=>{$('#bp_json').value=JSON.stringify(state.rows); $('#bpForm').submit();});
  $('#bpSearch')?.addEventListener('input',e=>{
    let q=e.target.value.toLowerCase(); 
    $$('.bp-block-item').forEach(el=>el.style.display=el.innerText.toLowerCase().includes(q)?'flex':'none')
  });
  $$('.bp-device button').forEach(b=>b.onclick=()=>{
    document.body.classList.remove('bp-preview-desktop','bp-preview-tablet','bp-preview-mobile');
    document.body.classList.add('bp-preview-'+b.dataset.device)
  });
}

// ==================== WIDGET DRAG-DROP ====================
function initDragDrop(){
  $$('.bp-widget').forEach(el=>{
    el.setAttribute('draggable', 'true');
    el.addEventListener('dragstart', handleWidgetDragStart);
    el.addEventListener('dragend', handleWidgetDragEnd);
    el.addEventListener('dragover', handleWidgetDragOver);
    el.addEventListener('dragenter', handleWidgetDragEnter);
    el.addEventListener('dragleave', handleWidgetDragLeave);
    el.addEventListener('drop', handleWidgetDrop);
  });
  
  $$('.bp-col').forEach(el=>{
    el.addEventListener('dragover', handleColDragOver);
    el.addEventListener('drop', handleColDrop);
    el.addEventListener('dragenter', handleColDragEnter);
    el.addEventListener('dragleave', handleColDragLeave);
  });
}

function handleWidgetDragStart(e){
  state.dragSrc = e.target;
  e.target.classList.add('bp-dragging');
  e.dataTransfer.effectAllowed = 'move';
  e.dataTransfer.setData('text/plain', e.target.dataset.wid);
}

function handleWidgetDragEnd(e){
  e.target.classList.remove('bp-dragging');
  $$('.bp-col, .bp-widget').forEach(el=>el.classList.remove('bp-drop-target','bp-drag-over'));
  state.dragSrc = null;
}

function handleWidgetDragOver(e){
  e.preventDefault();
  e.dataTransfer.dropEffect = 'move';
}

function handleWidgetDragEnter(e){
  e.preventDefault();
  const widget = e.target.closest('.bp-widget');
  if(widget && !widget.classList.contains('bp-dragging')){
    widget.classList.add('bp-drop-target');
  }
}

function handleWidgetDragLeave(e){
  const widget = e.target.closest('.bp-widget');
  if(widget){
    widget.classList.remove('bp-drop-target');
  }
}

function handleWidgetDrop(e){
  e.preventDefault();
  e.stopPropagation();
  if(!state.dragSrc) return;
  
  const srcWid = state.dragSrc.dataset.wid;
  const targetWid = e.target.closest('.bp-widget')?.dataset.wid;
  
  if(targetWid && targetWid !== srcWid){
    swapWidgets(srcWid, targetWid);
  }
  
  $$('.bp-widget, .bp-col').forEach(el=>el.classList.remove('bp-drop-target'));
}

function handleColDragOver(e){
  e.preventDefault();
  if(!state.dragSrc) return;
  const col = e.target.closest('.bp-col');
  if(col) col.classList.add('bp-drag-over');
}

function handleColDragEnter(e){
  e.preventDefault();
  const col = e.target.closest('.bp-col');
  if(col) col.classList.add('bp-drag-over');
}

function handleColDragLeave(e){
  const col = e.target.closest('.bp-col');
  if(col && !col.contains(e.relatedTarget)) {
    col.classList.remove('bp-drag-over');
  }
}

function handleColDrop(e){
  e.preventDefault();
  if(!state.dragSrc) return;
  
  const col = e.target.closest('.bp-col');
  if(!col) return;
  
  const srcWid = state.dragSrc.dataset.wid;
  const [rowId, colId] = (col.dataset.colId || '').split(':');
  
  if(rowId && colId){
    moveWidgetToCol(srcWid, rowId, colId);
  }
  
  $$('.bp-col').forEach(el=>el.classList.remove('bp-drag-over'));
}

function swapWidgets(srcWid, targetWid){
  snap();
  let srcWidget=null, srcCol=null;
  let tgtWidget=null, tgtCol=null;
  
  for(const row of state.rows){
    for(const col of row.cols){
      const sw = col.widgets.find(w=>w.id===srcWid);
      const tw = col.widgets.find(w=>w.id===targetWid);
      if(sw){srcWidget=sw;srcCol=col;}
      if(tw){tgtWidget=tw;tgtCol=col;}
    }
  }
  
  if(srcWidget && tgtWidget && srcCol && tgtCol){
    const srcIdx = srcCol.widgets.indexOf(srcWidget);
    const tgtIdx = tgtCol.widgets.indexOf(tgtWidget);
    
    if(srcCol.id === tgtCol.id){
      srcCol.widgets[srcIdx] = tgtWidget;
      srcCol.widgets[tgtIdx] = srcWidget;
    } else {
      srcCol.widgets.splice(srcIdx, 1);
      tgtCol.widgets.splice(tgtIdx, 1, srcWidget);
    }
    render(); initDragDrop(); initColResize(); initRowDragDrop(); initWidgetResize();
  }
}

function moveWidgetToCol(srcWid, targetRowId, targetColId){
  snap();
  let srcWidget=null, srcCol=null;
  
  for(const row of state.rows){
    for(const col of row.cols){
      const w = col.widgets.find(x=>x.id===srcWid);
      if(w){srcWidget=w;srcCol=col;break;}
    }
    if(srcWidget) break;
  }
  
  if(!srcWidget) return;
  
  const targetRow = state.rows.find(r=>r.id===targetRowId);
  const targetCol = targetRow?.cols.find(c=>c.id===targetColId);
  
  if(targetCol){
    srcCol.widgets = srcCol.widgets.filter(w=>w.id!==srcWid);
    targetCol.widgets.push(srcWidget);
    render(); initDragDrop(); initColResize(); initRowDragDrop(); initWidgetResize();
  }
}

// ==================== COLUMN RESIZE ====================
function initColResize(){
  $$('.bp-col-resize-handle').forEach(handle=>{
    handle.addEventListener('mousedown', startColResize);
  });
}

function startColResize(e){
  e.preventDefault();
  e.stopPropagation();
  
  const handle = e.target.closest('.bp-col-resize-handle');
  if(!handle) return;
  
  const key = handle.dataset.resize;
  const [rowId, colId] = key.split(':');
  const col = findCol(rowId, colId);
  if(!col) return;
  
  const startX = e.clientX;
  const startSpan = parseInt(col.span) || 1;
  let moved = false;
  
  function onMouseMove(e){
    const delta = e.clientX - startX;
    const spanDelta = Math.round(delta / 60);
    
    if(Math.abs(spanDelta) >= 1){
      const newSpan = Math.max(1, Math.min(10, startSpan + spanDelta));
      if(newSpan !== col.span){
        if(!moved) snap();
        moved = true;
        col.span = newSpan;
        render();
        initDragDrop();
        initColResize();
        initRowDragDrop();
        initWidgetResize();
      }
    }
  }
  
  function onMouseUp(){
    document.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseup', onMouseUp);
    if(moved){
      $('#bp_json').value = JSON.stringify(state.rows);
    }
  }
  
  document.addEventListener('mousemove', onMouseMove);
  document.addEventListener('mouseup', onMouseUp);
}

// ==================== ROW DRAG-DROP ====================
function initRowDragDrop(){
  $$('.bp-row').forEach(row=>{
    const dragHandle = row.querySelector('.bp-row-drag');
    if(dragHandle){
      dragHandle.setAttribute('draggable', 'true');
      dragHandle.addEventListener('dragstart', handleRowDragStart);
      dragHandle.addEventListener('dragend', handleRowDragEnd);
    }
    row.addEventListener('dragover', handleRowTargetDragOver);
    row.addEventListener('drop', handleRowTargetDrop);
  });
}

function handleRowDragStart(e){
  const row = e.target.closest('.bp-row');
  if(!row) return;
  state.dragRowSrc = row;
  row.classList.add('bp-dragging');
  e.dataTransfer.effectAllowed = 'move';
  e.dataTransfer.setData('text/plain', row.dataset.rowId || '');
}

function handleRowDragEnd(e){
  if(state.dragRowSrc) state.dragRowSrc.classList.remove('bp-dragging');
  $$('.bp-row').forEach(el=>el.classList.remove('bp-drag-over'));
  state.dragRowSrc = null;
}

function handleRowTargetDragOver(e){
  e.preventDefault();
  const row = e.target.closest('.bp-row');
  if(row && row !== state.dragRowSrc){
    row.classList.add('bp-drag-over');
  }
}

function handleRowTargetDrop(e){
  e.preventDefault();
  const targetRow = e.target.closest('.bp-row');
  if(!targetRow || !state.dragRowSrc) return;
  
  const srcId = state.dragRowSrc.dataset.rowId;
  const tgtId = targetRow.dataset.rowId;
  
  if(srcId && tgtId && srcId !== tgtId){
    swapRows(srcId, tgtId);
  }
  
  $$('.bp-row').forEach(el=>el.classList.remove('bp-drag-over'));
}

function swapRows(srcId, tgtId){
  snap();
  const srcIdx = state.rows.findIndex(r=>r.id===srcId);
  const tgtIdx = state.rows.findIndex(r=>r.id===tgtId);
  
  if(srcIdx >= 0 && tgtIdx >= 0){
    [state.rows[srcIdx], state.rows[tgtIdx]] = [state.rows[tgtIdx], state.rows[srcIdx]];
    render(); initDragDrop(); initColResize(); initRowDragDrop(); initWidgetResize();
  }
}

// ==================== WIDGET HEIGHT RESIZE ====================
function initWidgetResize(){
  $$('.bp-widget').forEach(widget=>{
    if(widget.querySelector('.bp-widget-resize-handle')) return;
    
    const handle = document.createElement('div');
    handle.className = 'bp-widget-resize-handle';
    handle.innerHTML = '⋮⋮';
    handle.title = 'Yüksekliği uzat/kısalt - sürükle';
    widget.appendChild(handle);
    
    handle.addEventListener('mousedown', startWidgetHeightResize);
  });
}

function startWidgetHeightResize(e){
  e.preventDefault();
  e.stopPropagation();
  
  const widget = e.target.closest('.bp-widget');
  if(!widget) return;
  
  const wid = widget.dataset.wid;
  const widgetData = findWidgetData(wid);
  if(!widgetData) return;
  
  const startY = e.clientY;
  const startHeight = widget.offsetHeight;
  let moved = false;
  
  function onMouseMove(e){
    const delta = e.clientY - startY;
    const newHeight = Math.max(60, startHeight + delta);
    widget.style.minHeight = newHeight + 'px';
    widget.dataset.userHeight = newHeight;
    moved = true;
  }
  
  function onMouseUp(){
    document.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseup', onMouseUp);
    if(moved && widgetData.widget){
      widgetData.widget.props = widgetData.widget.props || {};
      widgetData.widget.props.minHeight = widget.style.minHeight;
      $('#bp_json').value = JSON.stringify(state.rows);
    }
  }
  
  document.addEventListener('mousemove', onMouseMove);
  document.addEventListener('mouseup', onMouseUp);
}

function findWidgetData(wid){
  for(const row of state.rows){
    for(const col of row.cols){
      const w = col.widgets.find(x=>x.id===wid);
      if(w) return {row, col, widget: w};
    }
  }
  return null;
}

// ==================== ROW/COLUMN OPERATIONS ====================
function addRow(cols=1,save=true){
  if(save)snap(); 
  cols=Math.max(1,Math.min(10,cols)); 
  let span=Math.max(1,Math.floor(10/cols)); 
  let row={id:uid(),cols:[]}; 
  for(let i=0;i<cols;i++) row.cols.push({id:uid(),span:i===cols-1?10-span*(cols-1):span,widgets:[]}); 
  state.rows.push(row); 
  render();
  initDragDrop();
  initColResize();
  initRowDragDrop();
  initWidgetResize();
}

function addWidgetToFirst(type){
  snap(); 
  if(!state.rows.length)addRow(1,false); 
  let col=state.rows[0].cols[0]; 
  col.widgets.push(Object.assign({id:uid(),type,props:{}},defaultBlocks[type]||defaultBlocks.text)); 
  render();
  initDragDrop();
  initColResize();
  initWidgetResize();
}

function addWidget(rowId,colId,type){
  snap(); 
  let col=findCol(rowId,colId); 
  col.widgets.push(Object.assign({id:uid(),type,props:{}},defaultBlocks[type]||defaultBlocks.text)); 
  render();
  initDragDrop();
  initColResize();
  initWidgetResize();
}

function findCol(rid,cid){return state.rows.find(r=>r.id===rid)?.cols.find(c=>c.id===cid)}

// ==================== RENDER ====================
function render(){
  let c=$('#bpCanvas'); if(!c)return; 
  c.innerHTML=''; 
  
  if(!state.rows.length){
    c.innerHTML='<div class="bp-empty">Henüz satır yok. 1/1 - 1/10 arası kolon ekleyerek başlayın.</div>';
    return;
  }
  
  state.rows.forEach((row,ri)=>{
    let re=document.createElement('div');
    re.className='bp-row';
    re.dataset.rowId=row.id;
    re.innerHTML=`
      <div class="bp-row-head">
        <span class="bp-row-drag" title="Satırı sürükle">⋮⋮</span>
        <span class="bp-row-title">Satır ${ri+1} · ${row.cols.length} kolon</span>
        <span>
          <button type="button" class="bp-mini" data-row-up="${row.id}">↑</button>
          <button type="button" class="bp-mini" data-row-down="${row.id}">↓</button>
          <button type="button" class="bp-mini" data-row-del="${row.id}">Sil</button>
        </span>
      </div>
      <div class="bp-grid"></div>`;
    
    let grid=$('.bp-grid',re);
    row.cols.forEach((col,ci)=>{
      let ce=document.createElement('div');
      ce.className='bp-col';
      ce.dataset.colId=`${row.id}:${col.id}`;
      ce.style.setProperty('--span',col.span);
      ce.innerHTML=`
        <div class="bp-col-tools">
          <button type="button" class="bp-mini" data-add="${row.id}:${col.id}">+ Blok</button>
          <button type="button" class="bp-mini" data-less="${row.id}:${col.id}">Daralt</button>
          <button type="button" class="bp-mini" data-more="${row.id}:${col.id}">Genişlet</button>
        </div>
        <div class="bp-col-resize-handle" data-resize="${row.id}:${col.id}" title="Kolon genişliğini ayarla"></div>`;
      
      col.widgets.forEach(w=>ce.appendChild(widgetEl(row.id,col.id,w)));
      grid.appendChild(ce);
    });
    c.appendChild(re);
  });
  
  bindCanvas(); 
  initDragDrop(); 
  initColResize();
  initRowDragDrop();
  initWidgetResize();
  $('#bp_json').value=JSON.stringify(state.rows);
}

function widgetEl(rid,cid,w){
  let e=document.createElement('div');
  e.className='bp-widget'+(state.selected===w.id?' selected':'');
  if(w.props?.minHeight) e.style.minHeight = w.props.minHeight;
  e.draggable=true; 
  e.dataset.wid=w.id; 
  e.innerHTML=`
    <div class="bp-widget-type">${w.type}</div>
    ${renderWidgetContent(w)}
    <div class="bp-widget-actions">
      <button type="button" class="bp-mini" data-edit="${rid}:${cid}:${w.id}">Düzenle</button>
      <button type="button" class="bp-mini" data-copy="${rid}:${cid}:${w.id}">Kopyala</button>
      <button type="button" class="bp-mini" data-del="${rid}:${cid}:${w.id}">Sil</button>
    </div>`; 
  return e;
}

function renderWidgetContent(w){
  if(w.type==='product'||w.type==='pricing')return `<h3>${esc(w.title)}</h3><p>${esc(w.text)}</p><div class="price">${esc(w.price||'')}</div><span class="cta">Satın Al</span>`; 
  if(w.type==='hero')return `<h2>${esc(w.title)}</h2><p>${esc(w.text)}</p><span class="cta">${esc(w.button||'Başla')}</span>`; 
  if(w.type==='domain')return `<h3>${esc(w.title)}</h3><p>${esc(w.text)}</p><input disabled value="ornekdomain.com" style="width:100%;padding:10px;border-radius:12px;border:1px solid #dbe5f2"><span class="cta" style="margin-top:8px">Sorgula</span>`; 
  return `<h3>${esc(w.title||'Blok')}</h3><p>${esc(w.text||'')}</p>`;
}

function esc(s){return String(s||'').replace(/[&<>]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]))}

// ==================== CANVAS BINDINGS ====================
function bindCanvas(){
  $$('[data-row-del]').forEach(b=>b.onclick=()=>{snap();state.rows=state.rows.filter(r=>r.id!==b.dataset.rowDel);render();});
  $$('[data-row-up]').forEach(b=>b.onclick=()=>moveRow(b.dataset.rowUp,-1)); 
  $$('[data-row-down]').forEach(b=>b.onclick=()=>moveRow(b.dataset.rowDown,1));
  $$('[data-add]').forEach(b=>b.onclick=()=>{let [r,c]=b.dataset.add.split(':');addWidget(r,c,'text')});
  $$('[data-less]').forEach(b=>b.onclick=()=>resizeCol(b.dataset.less,-1)); 
  $$('[data-more]').forEach(b=>b.onclick=()=>resizeCol(b.dataset.more,1));
  $$('[data-edit]').forEach(b=>b.onclick=()=>selectWidget(b.dataset.edit)); 
  $$('[data-del]').forEach(b=>b.onclick=()=>delWidget(b.dataset.del)); 
  $$('[data-copy]').forEach(b=>b.onclick=()=>copyWidget(b.dataset.copy));
}

function moveRow(id,dir){
  let i=state.rows.findIndex(r=>r.id===id);
  let j=i+dir;
  if(i<0||j<0||j>=state.rows.length)return;
  snap();
  [state.rows[i],state.rows[j]]=[state.rows[j],state.rows[i]];
  render();
}

function resizeCol(key,delta){
  let [r,c]=key.split(':'); 
  let col=findCol(r,c); 
  if(!col)return; 
  snap(); 
  col.span=Math.max(1,Math.min(10,(parseInt(col.span)||1)+delta)); 
  render();
}

function findWidget(key){
  let [r,c,w]=key.split(':'); 
  let col=findCol(r,c); 
  return [col,col.widgets.find(x=>x.id===w)];
}

function selectWidget(key){
  let [col,w]=findWidget(key); 
  if(!w)return; 
  state.selected=w.id; 
  renderInspector(key,w); 
  render();
}

function delWidget(key){
  let [col,w]=findWidget(key); 
  if(!w)return; 
  snap(); 
  col.widgets=col.widgets.filter(x=>x.id!==w.id); 
  state.selected=null; 
  renderInspector(null,null); 
  render();
}

function copyWidget(key){
  let [col,w]=findWidget(key); 
  if(!w)return; 
  snap(); 
  let nw=JSON.parse(JSON.stringify(w)); 
  nw.id=uid(); 
  nw.title+=' Kopya'; 
  col.widgets.push(nw); 
  render();
}

function renderInspector(key,w){
  let p=$('#bpInspector'); 
  if(!p)return; 
  if(!w){p.innerHTML='<p>Bir blok seçin.</p>';return;} 
  p.innerHTML=`
    <div class="bp-inspector-field"><label>Başlık</label><input id="iTitle" value="${esc(w.title)}"></div>
    <div class="bp-inspector-field"><label>Metin</label><textarea id="iText">${esc(w.text)}</textarea></div>
    <div class="bp-inspector-field"><label>Fiyat / Ek Değer</label><input id="iPrice" value="${esc(w.price||'')}"></div>
    <div class="bp-inspector-field"><label>Buton</label><input id="iButton" value="${esc(w.button||'')}"></div>
    <button type="button" class="bp-btn" id="iApply">Uygula</button>`; 
  $('#iApply').onclick=()=>{
    snap(); 
    w.title=$('#iTitle').value; 
    w.text=$('#iText').value; 
    w.price=$('#iPrice').value; 
    w.button=$('#iButton').value; 
    render(); 
    renderInspector(key,w);
  };
}

// ==================== START ====================
document.addEventListener('DOMContentLoaded',init);
})();
