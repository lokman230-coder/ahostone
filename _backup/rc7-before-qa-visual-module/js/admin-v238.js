document.addEventListener('click',function(e){
  const btn=e.target.closest('[data-ao-tab]'); if(btn){ const wrap=btn.closest('[data-ao-tabs]'); if(!wrap) return; wrap.querySelectorAll('[data-ao-tab]').forEach(b=>b.classList.remove('active')); wrap.querySelectorAll('[data-ao-panel]').forEach(p=>p.classList.remove('active')); btn.classList.add('active'); const p=wrap.querySelector('[data-ao-panel="'+btn.dataset.aoTab+'"]'); if(p) p.classList.add('active'); }
  const c=e.target.closest('[data-confirm]'); if(c && !confirm(c.dataset.confirm||'Bu işlemi onaylıyor musunuz?')) e.preventDefault();
});
