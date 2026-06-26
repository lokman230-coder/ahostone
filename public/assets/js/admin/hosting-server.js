(function(){
  document.addEventListener('click', function(e){
    var btn=e.target.closest('[data-server-test-note]');
    if(btn) console.log('Server test', btn.getAttribute('data-server-test-note'));
  });
})();
