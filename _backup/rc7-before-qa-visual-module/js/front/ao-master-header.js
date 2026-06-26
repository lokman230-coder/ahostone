// Ahost One v24.11.7 - header/dropdown interaction polish only.
(function(){
  function closest(el, selector){ return el && el.closest ? el.closest(selector) : null; }
  document.addEventListener('click', function(e){
    var dropdown = closest(e.target, '.ao-login-dropdown,.ao-account-dropdown,.ao-lang-dropdown');
    document.querySelectorAll('.ao-login-dropdown.is-open,.ao-account-dropdown.is-open,.ao-lang-dropdown.is-open').forEach(function(item){
      if(item !== dropdown) item.classList.remove('is-open');
    });
    var btn = closest(e.target, '.ao-login-btn,.ao-account-btn,.ao-lang-btn');
    if(btn && dropdown){
      e.preventDefault();
      dropdown.classList.toggle('is-open');
    }
  });
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') document.querySelectorAll('.ao-login-dropdown.is-open,.ao-account-dropdown.is-open,.ao-lang-dropdown.is-open').forEach(function(item){ item.classList.remove('is-open'); });
  });
})();
