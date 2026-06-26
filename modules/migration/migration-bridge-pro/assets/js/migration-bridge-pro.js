document.addEventListener('DOMContentLoaded',()=>{
  const tabs=[...document.querySelectorAll('.mbp-tab')];
  const panels=[...document.querySelectorAll('.mbp-panel')];
  function openTab(id){tabs.forEach(t=>t.classList.toggle('active',t.dataset.tab===id));panels.forEach(p=>p.classList.toggle('active',p.dataset.panel===id));}
  if(tabs[0]) openTab(tabs[0].dataset.tab);
  tabs.forEach(t=>t.addEventListener('click',()=>openTab(t.dataset.tab)));
  document.querySelectorAll('.mbp-search').forEach(input=>input.addEventListener('input',()=>{const q=input.value.toLowerCase();input.closest('.mbp-panel').querySelectorAll('tbody tr').forEach(tr=>tr.style.display=tr.textContent.toLowerCase().includes(q)?'':'none')}));
  document.querySelector('[data-select-all]')?.addEventListener('click',()=>document.querySelectorAll('td select').forEach(s=>s.value='import'));
  document.querySelector('[data-skip-all]')?.addEventListener('click',()=>document.querySelectorAll('td select').forEach(s=>s.value='skip'));
});
