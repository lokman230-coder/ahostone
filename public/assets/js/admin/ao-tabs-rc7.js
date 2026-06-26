(function(){
  function activate(scope, key){
    if(!key) return;
    scope.querySelectorAll('[data-ao-tab]').forEach(function(btn){btn.classList.toggle('active', btn.getAttribute('data-ao-tab')===key); btn.setAttribute('aria-selected', btn.classList.contains('active')?'true':'false');});
    scope.querySelectorAll('[data-ao-panel]').forEach(function(panel){var on=panel.getAttribute('data-ao-panel')===key; panel.classList.toggle('active',on); panel.hidden=!on;});
  }
  function init(root){
    (root||document).querySelectorAll('[data-ao-tabs]').forEach(function(scope){
      if(scope.dataset.aoTabsReady==='1') return; scope.dataset.aoTabsReady='1';
      var first=scope.querySelector('[data-ao-tab].active')||scope.querySelector('[data-ao-tab]');
      var key=(location.hash||'').replace('#','');
      if(key && !scope.querySelector('[data-ao-tab="'+CSS.escape(key)+'"]')) key='';
      activate(scope, key || (first && first.getAttribute('data-ao-tab')));
      scope.addEventListener('click', function(e){var btn=e.target.closest('[data-ao-tab]'); if(!btn || !scope.contains(btn)) return; e.preventDefault(); activate(scope, btn.getAttribute('data-ao-tab')); if(btn.getAttribute('data-ao-tab')) history.replaceState(null,'','#'+btn.getAttribute('data-ao-tab'));});
    });
  }
  document.addEventListener('DOMContentLoaded', function(){init(document);});
  window.AhostTabsRC7={init:init,activate:activate};
})();
