document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('[data-production-refresh]').forEach(function(btn){
    btn.addEventListener('click', function(){ window.location.href = (window.AHOST_BASE_URL || '') + '/admin/production-test'; });
  });
});
