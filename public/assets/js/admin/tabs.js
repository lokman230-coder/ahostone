(function(){
  document.addEventListener('click',function(e){
    var tabBtn=e.target.closest('[data-ao-tabs] .ao-real-tabs button[data-tab]');
    if(!tabBtn) return;
    var shell=tabBtn.closest('[data-ao-tabs]');
    shell.querySelectorAll('.ao-real-tabs button').forEach(function(b){b.classList.remove('active');});
    shell.querySelectorAll('.ao-tab-panel').forEach(function(p){p.classList.remove('active');});
    tabBtn.classList.add('active');
    var panel=shell.querySelector('#tab-'+tabBtn.dataset.tab);
    if(panel) panel.classList.add('active');
  });
})();
