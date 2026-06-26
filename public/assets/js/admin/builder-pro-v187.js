
(function(){
const $=(s,p=document)=>p.querySelector(s), $$=(s,p=document)=>Array.from(p.querySelectorAll(s));
const state={target:'site',template:'home',rows:[],selected:null,history:[],future:[]};
const defaultBlocks={hero:{title:'Premium Hero',text:'Domain, hosting, marketplace ve AI tek panelde.',button:'Hemen Başla'},product:{title:'Ürün/Paket',text:'Hosting, VPS, web tasarım veya lisans ürünü.',price:'₺149/ay'},domain:{title:'Domain Search Center',text:'Registrar fiyat + komisyon ile canlı satış fiyatı.'},kpi:{title:'Dashboard KPI',text:'MRR, ARR, müşteri, domain, ticket SLA.'},renewal:{title:'Yenileme Kartı',text:'Hosting/domain ödeme tarihi ve kalan gün.'},invoice:{title:'Fatura Kartı',text:'Son faturalar, ödeme durumu ve tahsilat.'},ticket:{title:'Ticket Kartı',text:'Açık destek kayıtları ve SLA.'},text:{title:'Metin Bloğu',text:'Tanıtım, açıklama veya içerik alanı.'},pricing:{title:'Fiyat Tablosu',text:'Başlangıç / Pro / Enterprise karşılaştırması.',price:'₺299/ay'},testimonial:{title:'Müşteri Yorumu',text:'Ahost One operasyonu tek panele topladı.'},faq:{title:'SSS',text:'Sık sorulan soru ve cevap bloğu.'},chart:{title:'Grafik',text:'Gelir, sipariş, kaynak kullanımı veya SLA grafiği.'},form:{title:'Form',text:'Teklif, destek, başvuru veya iletişim formu.'},media:{title:'Medya',text:'Görsel, video, galeri veya slider.'}};
function uid(){return 'bp_'+Math.random().toString(36).slice(2,9)}
function snap(){state.history.push(JSON.stringify(state.rows)); if(state.history.length>40)state.history.shift(); state.future=[];}
function init(){let raw=$('#bp_json')?.value||'[]'; try{state.rows=JSON.parse(raw)||[]}catch(e){state.rows=[]} if(!state.rows.length) addRow(2,false); render(); bind();}
function bind(){
 $$('.bp-target').forEach(b=>b.onclick=()=>{state.target=b.dataset.target; $$('.bp-target').forEach(x=>x.classList.toggle('active',x===b)); $('#bp_target').value=state.target;});
 $$('.bp-block-item').forEach(b=>b.onclick=()=>addWidgetToFirst(b.dataset.type));
 $('#bpAddRow')?.addEventListener('click',()=>addRow(parseInt($('#bpCols').value||'1'),true));
 $('#bpUndo')?.addEventListener('click',()=>{if(!state.history.length)return; state.future.push(JSON.stringify(state.rows)); state.rows=JSON.parse(state.history.pop()); render();});
 $('#bpRedo')?.addEventListener('click',()=>{if(!state.future.length)return; state.history.push(JSON.stringify(state.rows)); state.rows=JSON.parse(state.future.pop()); render();});
 $('#bpSave')?.addEventListener('click',()=>{$('#bp_json').value=JSON.stringify(state.rows); $('#bpForm').submit();});
 $('#bpSearch')?.addEventListener('input',e=>{let q=e.target.value.toLowerCase(); $$('.bp-block-item').forEach(el=>el.style.display=el.innerText.toLowerCase().includes(q)?'flex':'none')});
 $$('.bp-device button').forEach(b=>b.onclick=()=>{document.body.classList.remove('bp-preview-desktop','bp-preview-tablet','bp-preview-mobile');document.body.classList.add('bp-preview-'+b.dataset.device)});
}
function addRow(cols=1,save=true){if(save)snap(); cols=Math.max(1,Math.min(10,cols)); let span=Math.max(1,Math.floor(10/cols)); let row={id:uid(),cols:[]}; for(let i=0;i<cols;i++) row.cols.push({id:uid(),span:i===cols-1?10-span*(cols-1):span,widgets:[]}); state.rows.push(row); render();}
function addWidgetToFirst(type){snap(); if(!state.rows.length)addRow(1,false); let col=state.rows[0].cols[0]; col.widgets.push(Object.assign({id:uid(),type},defaultBlocks[type]||defaultBlocks.text)); render();}
function addWidget(rowId,colId,type){snap(); let col=findCol(rowId,colId); col.widgets.push(Object.assign({id:uid(),type},defaultBlocks[type]||defaultBlocks.text)); render();}
function findCol(rid,cid){return state.rows.find(r=>r.id===rid).cols.find(c=>c.id===cid)}
function render(){let c=$('#bpCanvas'); if(!c)return; c.innerHTML=''; if(!state.rows.length)c.innerHTML='<div class="bp-empty">Henüz satır yok. 1/1 - 1/10 arası kolon ekleyerek başlayın.</div>';
 state.rows.forEach((row,ri)=>{let re=document.createElement('div');re.className='bp-row';re.innerHTML=`<div class="bp-row-head"><span class="bp-row-title">Satır ${ri+1} · ${row.cols.length} kolon</span><span><button type="button" class="bp-mini" data-row-up="${row.id}">↑</button><button type="button" class="bp-mini" data-row-down="${row.id}">↓</button><button type="button" class="bp-mini" data-row-del="${row.id}">Sil</button></span></div><div class="bp-grid"></div>`; let grid=$('.bp-grid',re);
 row.cols.forEach(col=>{let ce=document.createElement('div');ce.className='bp-col';ce.style.setProperty('--span',col.span);ce.innerHTML=`<div class="bp-col-tools"><button type="button" class="bp-mini" data-add="${row.id}:${col.id}">+ Blok</button><button type="button" class="bp-mini" data-less="${row.id}:${col.id}">-</button><button type="button" class="bp-mini" data-more="${row.id}:${col.id}">+</button></div><div class="bp-col-resize" data-resize="${row.id}:${col.id}"></div>`;
 col.widgets.forEach(w=>ce.appendChild(widgetEl(row.id,col.id,w))); grid.appendChild(ce);}); c.appendChild(re);});
 bindCanvas(); $('#bp_json').value=JSON.stringify(state.rows);
}
function widgetEl(rid,cid,w){let e=document.createElement('div');e.className='bp-widget'+(state.selected===w.id?' selected':''); e.draggable=true; e.dataset.wid=w.id; e.innerHTML=`<div class="bp-widget-type">${w.type}</div>${renderWidgetContent(w)}<div style="display:flex;gap:6px;margin-top:10px"><button type="button" class="bp-mini" data-edit="${rid}:${cid}:${w.id}">Düzenle</button><button type="button" class="bp-mini" data-copy="${rid}:${cid}:${w.id}">Kopyala</button><button type="button" class="bp-mini" data-del="${rid}:${cid}:${w.id}">Sil</button></div>`; return e;}
function renderWidgetContent(w){if(w.type==='product'||w.type==='pricing')return `<h3>${esc(w.title)}</h3><p>${esc(w.text)}</p><div class="price">${esc(w.price||'')}</div><span class="cta">Satın Al</span>`; if(w.type==='hero')return `<h2>${esc(w.title)}</h2><p>${esc(w.text)}</p><span class="cta">${esc(w.button||'Başla')}</span>`; if(w.type==='domain')return `<h3>${esc(w.title)}</h3><p>${esc(w.text)}</p><input disabled value="ornekdomain.com" style="width:100%;padding:10px;border-radius:12px;border:1px solid #dbe5f2"><span class="cta" style="margin-top:8px">Sorgula</span>`; return `<h3>${esc(w.title||'Blok')}</h3><p>${esc(w.text||'')}</p>`;}
function esc(s){return String(s||'').replace(/[&<>]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]))}
function bindCanvas(){
 $$('[data-row-del]').forEach(b=>b.onclick=()=>{snap();state.rows=state.rows.filter(r=>r.id!==b.dataset.rowDel);render();});
 $$('[data-row-up]').forEach(b=>b.onclick=()=>moveRow(b.dataset.rowUp,-1)); $$('[data-row-down]').forEach(b=>b.onclick=()=>moveRow(b.dataset.rowDown,1));
 $$('[data-add]').forEach(b=>b.onclick=()=>{let [r,c]=b.dataset.add.split(':');addWidget(r,c,'text')});
 $$('[data-less]').forEach(b=>b.onclick=()=>resizeCol(b.dataset.less,-1)); $$('[data-more]').forEach(b=>b.onclick=()=>resizeCol(b.dataset.more,1));
 $$('[data-resize]').forEach(h=>{h.onmousedown=(ev)=>{ev.preventDefault(); let start=ev.clientX; let key=h.dataset.resize; let move=(e)=>{if(Math.abs(e.clientX-start)>40){resizeCol(key,e.clientX>start?1:-1);start=e.clientX}}; let up=()=>{document.removeEventListener('mousemove',move);document.removeEventListener('mouseup',up)};document.addEventListener('mousemove',move);document.addEventListener('mouseup',up)}});
 $$('[data-edit]').forEach(b=>b.onclick=()=>selectWidget(b.dataset.edit)); $$('[data-del]').forEach(b=>b.onclick=()=>delWidget(b.dataset.del)); $$('[data-copy]').forEach(b=>b.onclick=()=>copyWidget(b.dataset.copy));
}
function moveRow(id,dir){let i=state.rows.findIndex(r=>r.id===id);let j=i+dir;if(i<0||j<0||j>=state.rows.length)return;snap();[state.rows[i],state.rows[j]]=[state.rows[j],state.rows[i]];render();}
function resizeCol(key,delta){let [r,c]=key.split(':'), col=findCol(r,c); if(!col)return; snap(); col.span=Math.max(1,Math.min(10,(parseInt(col.span)||1)+delta)); render();}
function findWidget(key){let [r,c,w]=key.split(':'), col=findCol(r,c); return [col,col.widgets.find(x=>x.id===w)];}
function selectWidget(key){let [col,w]=findWidget(key); if(!w)return; state.selected=w.id; renderInspector(key,w); render();}
function delWidget(key){let [col,w]=findWidget(key); if(!w)return; snap(); col.widgets=col.widgets.filter(x=>x.id!==w.id); state.selected=null; renderInspector(null,null); render();}
function copyWidget(key){let [col,w]=findWidget(key); if(!w)return; snap(); let nw=JSON.parse(JSON.stringify(w)); nw.id=uid(); nw.title+=' Kopya'; col.widgets.push(nw); render();}
function renderInspector(key,w){let p=$('#bpInspector'); if(!p)return; if(!w){p.innerHTML='<p>Bir blok seçin.</p>';return;} p.innerHTML=`<div class="bp-inspector-field"><label>Başlık</label><input id="iTitle" value="${esc(w.title)}"></div><div class="bp-inspector-field"><label>Metin</label><textarea id="iText">${esc(w.text)}</textarea></div><div class="bp-inspector-field"><label>Fiyat / Ek Değer</label><input id="iPrice" value="${esc(w.price||'')}"></div><div class="bp-inspector-field"><label>Buton</label><input id="iButton" value="${esc(w.button||'')}"></div><button type="button" class="bp-btn" id="iApply">Uygula</button>`; $('#iApply').onclick=()=>{snap(); w.title=$('#iTitle').value; w.text=$('#iText').value; w.price=$('#iPrice').value; w.button=$('#iButton').value; render(); renderInspector(key,w);};}
document.addEventListener('DOMContentLoaded',init);
})();
