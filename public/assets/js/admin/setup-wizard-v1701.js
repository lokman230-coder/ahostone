
document.addEventListener('DOMContentLoaded', function(){
  const wizard = document.getElementById('aoSetupWizard');
  if(!wizard) return;
  const panels = Array.from(wizard.querySelectorAll('.wizard-step-panel'));
  const tabs = Array.from(wizard.querySelectorAll('.wizard-tab'));
  const next = wizard.querySelector('[data-wizard-next]');
  const prev = wizard.querySelector('[data-wizard-prev]');
  const fill = wizard.querySelector('.wizard-progress-fill');
  let current = 0;
  function show(i){
    current = Math.max(0, Math.min(i, panels.length-1));
    panels.forEach((p,idx)=>p.classList.toggle('active', idx===current));
    tabs.forEach((t,idx)=>t.classList.toggle('active', idx===current));
    if(prev) prev.disabled = current===0;
    if(next) next.textContent = current===panels.length-1 ? 'Son Bölüm' : 'Sonraki →';
    if(fill) fill.style.width = Math.round(((current+1)/panels.length)*100)+'%';
    try{localStorage.setItem('ahost_setup_wizard_step', String(current));}catch(e){}
    window.scrollTo({top:0, behavior:'smooth'});
  }
  tabs.forEach((t,idx)=>t.addEventListener('click',()=>show(idx)));
  if(next) next.addEventListener('click',()=>show(current+1));
  if(prev) prev.addEventListener('click',()=>show(current-1));
  try{ const saved=parseInt(localStorage.getItem('ahost_setup_wizard_step')||'0',10); if(saved>0 && saved<panels.length) current=saved; }catch(e){}
  show(current);
  wizard.querySelectorAll('.setup-step select').forEach(function(sel){
    sel.addEventListener('change', function(){
      const card = sel.closest('.setup-step');
      if(!card) return;
      card.classList.remove('status-pending','status-done','status-skipped');
      card.classList.add('status-'+sel.value);
    });
  });
});
