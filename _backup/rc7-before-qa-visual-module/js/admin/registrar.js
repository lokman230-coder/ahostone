(function(){
  function closeOtherRegistrars(current){
    document.querySelectorAll('[data-registrar-item].open').forEach(function(item){
      if(item!==current){
        item.classList.remove('open');
        var body=item.querySelector('[data-registrar-body]');
        var btn=item.querySelector('[data-registrar-toggle]');
        if(body) body.hidden=true;
        if(btn) btn.setAttribute('aria-expanded','false');
      }
    });
  }
  function updateAuthFields(form){
    if(!form) return;
    var mode=(form.querySelector('[data-auth-mode]')||{}).value || 'userpass';
    form.querySelectorAll('[data-auth-field]').forEach(function(el){
      var modes=(el.getAttribute('data-auth-field')||'').split(/\s+/);
      el.classList.toggle('is-hidden', modes.indexOf(mode)===-1);
    });
  }
  document.addEventListener('click',function(e){
    var btn=e.target.closest('[data-registrar-toggle]');
    if(!btn) return;
    e.preventDefault();
    var item=btn.closest('[data-registrar-item]');
    var body=item && item.querySelector('[data-registrar-body]');
    if(!item || !body) return;
    var willOpen=!item.classList.contains('open');
    closeOtherRegistrars(item);
    item.classList.toggle('open', willOpen);
    body.hidden=!willOpen;
    btn.setAttribute('aria-expanded', willOpen ? 'true':'false');
    if(willOpen) localStorage.setItem('ao_open_registrar_id', item.getAttribute('data-registrar-id')||'');
  });
  document.addEventListener('change',function(e){ if(e.target.matches('[data-auth-mode]')) updateAuthFields(e.target.closest('[data-auth-form]')); });
  document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('[data-auth-form]').forEach(updateAuthFields);
    var saved=localStorage.getItem('ao_open_registrar_id');
    if(saved){
      var sel='[data-registrar-item][data-registrar-id="'+(window.CSS&&CSS.escape?CSS.escape(saved):saved.replace(/"/g,''))+'"]';
      var item=document.querySelector(sel);
      if(item){ item.classList.add('open'); var body=item.querySelector('[data-registrar-body]'); var btn=item.querySelector('[data-registrar-toggle]'); if(body) body.hidden=false; if(btn) btn.setAttribute('aria-expanded','true'); }
    }
  });
})();
