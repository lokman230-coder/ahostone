(function(){
  document.addEventListener('click',function(e){
    var btn=e.target.closest('[data-copy-scan]');
    if(!btn) return;
    var text=document.querySelector(btn.getAttribute('data-copy-scan'));
    if(text && navigator.clipboard) navigator.clipboard.writeText(text.innerText||text.textContent||'');
  });
})();
