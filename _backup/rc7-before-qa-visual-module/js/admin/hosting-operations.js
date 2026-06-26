document.addEventListener('click', function(e){
  var btn=e.target.closest('[data-reveal-secret]');
  if(!btn) return;
  var code=btn.parentElement.querySelector('.ao-secret');
  if(!code) return;
  var secret=code.getAttribute('data-secret') || '';
  if(code.dataset.visible==='1') { code.textContent='••••••••'; code.dataset.visible='0'; btn.textContent='Göster'; }
  else { code.textContent=secret || '-'; code.dataset.visible='1'; btn.textContent='Gizle'; }
});
