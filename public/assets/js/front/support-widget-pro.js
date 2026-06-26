(function(){
  const modal=document.querySelector('[data-support-modal]');
  if(!modal) return;
  function setTab(name){
    modal.querySelectorAll('[data-support-tab]').forEach(b=>b.classList.toggle('active',b.dataset.supportTab===name));
    modal.querySelectorAll('[data-support-pane]').forEach(p=>p.classList.toggle('active',p.dataset.supportPane===name));
  }
  function openSupport(tab){ modal.hidden=false; modal.classList.add('is-open'); if(tab) setTab(tab); }
  function closeSupport(){ modal.classList.remove('is-open'); modal.hidden=true; }
  document.addEventListener('click',function(e){
    const opener=e.target.closest('[data-support-open]');
    if(opener){ e.preventDefault(); openSupport(opener.dataset.mobileTab || opener.dataset.supportOpen || null); return; }
    const tab=e.target.closest('[data-support-tab]');
    if(tab){ e.preventDefault(); setTab(tab.dataset.supportTab); return; }
    if(e.target.closest('[data-support-close]')){ e.preventDefault(); closeSupport(); return; }
    if(!modal.hidden && e.target===modal) closeSupport();
  },true);
  document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeSupport(); });
  const mb=document.querySelector('[data-mobile-support]'), panel=document.querySelector('[data-mobile-support-panel]');
  if(mb&&panel){ mb.addEventListener('click',e=>{e.preventDefault(); panel.hidden=!panel.hidden;}); document.addEventListener('click',e=>{if(!panel.hidden&&!panel.contains(e.target)&&!mb.contains(e.target)) panel.hidden=true;}); }
  const searchInput=modal.querySelector('[data-support-search-input]');
  const searchResults=modal.querySelector('[data-support-search-results]');
  let timer=null;
  if(searchInput&&searchResults){
    searchInput.addEventListener('input',function(){ clearTimeout(timer); const q=this.value.trim(); if(q.length<2){ searchResults.innerHTML='<p>En az 2 karakter yazın.</p>'; return; } timer=setTimeout(()=>{ fetch((window.AHOST_BASE_URL||'')+'/support/widget/search?q='+encodeURIComponent(q)).then(r=>r.json()).then(d=>{ const items=d.items||[]; searchResults.innerHTML=items.length?items.map(it=>'<div class="ao-support-result"><a href="'+(it.url||'#')+'">'+escapeHtml(it.title||'Makale')+'</a><p>'+escapeHtml(it.excerpt||'')+'</p></div>').join(''):'<p>Sonuç bulunamadı. AI destek veya canlı sohbeti deneyin.</p>'; }).catch(()=>{searchResults.innerHTML='<p>Arama yapılamadı.</p>';}); },350); });
  }
  const aiForm=modal.querySelector('[data-support-ai-form]');
  const aiResult=modal.querySelector('[data-support-ai-result]');
  if(aiForm&&aiResult){ aiForm.addEventListener('submit',function(e){ e.preventDefault(); const fd=new FormData(aiForm); aiResult.innerHTML='<p>Cevap aranıyor...</p>'; fetch((window.AHOST_BASE_URL||'')+'/support/widget/ai',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{ let html='<div class="ao-support-result"><p>'+escapeHtml(d.answer||'Cevap bulunamadı.')+'</p></div>'; if(d.handoff) html+='<button type="button" class="ao-support-submit" data-support-tab="live">Canlı Temsilciye Aktar</button>'; aiResult.innerHTML=html; }).catch(()=>{aiResult.innerHTML='<p>AI cevap alınamadı.</p>';}); }); }
  function escapeHtml(s){ return String(s||'').replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
})();