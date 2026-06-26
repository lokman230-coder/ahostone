(function(){
  document.addEventListener('click', function(e){
    var destructive=e.target.closest('[data-confirm]');
    if(destructive && !confirm(destructive.getAttribute('data-confirm'))) e.preventDefault();
  });
})();
