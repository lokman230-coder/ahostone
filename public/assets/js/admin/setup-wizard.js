document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.setup-step select').forEach(function(sel){
    sel.addEventListener('change', function(){
      const card = sel.closest('.setup-step');
      if(!card) return;
      card.classList.remove('status-pending','status-done','status-skipped');
      card.classList.add('status-'+sel.value);
    });
  });
});
