
(function(){
  function q(s,r){return (r||document).querySelector(s)}
  function qa(s,r){return Array.from((r||document).querySelectorAll(s))}
  function refreshPreview(){
    var title=q('[name="app_name"]'); var color=q('[name="primary_color"]'); var sector=q('[name="sector"]');
    var h=q('[data-mb-preview-title]'); if(h&&title) h.textContent=title.value||'Ahost Mobil Uygulama';
    var s=q('[data-mb-preview-sector]'); if(s&&sector) s.textContent=sector.value||'Genel';
    var hero=q('.mb-hero'); if(hero&&color&&color.value) hero.style.background='linear-gradient(135deg,'+color.value+',#7c3aed)';
  }
  document.addEventListener('input',function(e){ if(e.target.closest('.mb-live-form')) refreshPreview(); });
  document.addEventListener('click',function(e){
    var tool=e.target.closest('[data-add-widget]');
    if(tool){ var list=q('[data-mb-widget-list]'); if(list){ var div=document.createElement('div'); div.className='mb-card'; div.textContent=tool.getAttribute('data-add-widget')+' bileşeni eklendi'; list.appendChild(div);} }
    var ai=e.target.closest('[data-ai-mobile-demo]');
    if(ai){ e.preventDefault(); var prompt=q('[name="ai_prompt"]'); var name=q('[name="app_name"]'); var sector=q('[name="sector"]'); if(prompt&&name){ name.value=(prompt.value||'Akıllı Mobil Uygulama').split(' ').slice(0,4).join(' '); } if(sector){ sector.value='AI Tasarım'; } refreshPreview(); }
  });
  document.addEventListener('DOMContentLoaded', refreshPreview);
})();
