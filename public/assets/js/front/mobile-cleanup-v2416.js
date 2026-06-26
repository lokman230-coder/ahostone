(function(){
  function closeDrawer(){var d=document.querySelector('[data-site-mobile-drawer]');var b=document.querySelector('[data-site-mobile-menu]');if(d){d.hidden=true;}if(b){b.setAttribute('aria-expanded','false');}}
  document.addEventListener('click',function(e){
    var open=e.target.closest('[data-site-mobile-menu]');
    if(open){e.preventDefault();var d=document.querySelector('[data-site-mobile-drawer]');if(!d)return;d.hidden=!d.hidden;open.setAttribute('aria-expanded',d.hidden?'false':'true');return;}
    if(e.target.closest('[data-site-mobile-close]')){e.preventDefault();closeDrawer();return;}
    var drawer=document.querySelector('[data-site-mobile-drawer]');
    if(drawer && !drawer.hidden && !drawer.contains(e.target) && !e.target.closest('[data-site-mobile-menu]')) closeDrawer();
    var mobileSupport=e.target.closest('[data-mobile-support]');
    if(mobileSupport){e.preventDefault();var panel=document.querySelector('[data-mobile-support-panel]');if(panel) panel.hidden=!panel.hidden;}
    var tab=e.target.closest('[data-mobile-tab]');
    if(tab){var name=tab.getAttribute('data-mobile-tab');setTimeout(function(){document.querySelectorAll('[data-support-tab]').forEach(function(btn){if(btn.getAttribute('data-support-tab')===name) btn.click();});},20);}
  });
  document.addEventListener('keydown',function(e){if(e.key==='Escape')closeDrawer();});
})();
