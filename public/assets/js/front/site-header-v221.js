(function(){
  function ready(fn){document.readyState!=='loading'?fn():document.addEventListener('DOMContentLoaded',fn)}
  ready(function(){
    document.querySelectorAll('[data-client-dropdown]').forEach(function(box){
      var btn=box.querySelector('.client-login-toggle'); var timer=null;
      function open(){clearTimeout(timer); box.classList.add('hover-open','open'); if(btn)btn.setAttribute('aria-expanded','true')}
      function close(){clearTimeout(timer); timer=setTimeout(function(){box.classList.remove('hover-open','open'); if(btn)btn.setAttribute('aria-expanded','false')},180)}
      box.addEventListener('mouseenter',open); box.addEventListener('mouseleave',close);
      if(btn){btn.addEventListener('click',function(e){ if(window.matchMedia('(max-width: 980px)').matches){e.preventDefault(); box.classList.toggle('open'); btn.setAttribute('aria-expanded', box.classList.contains('open')?'true':'false')}})}
      document.addEventListener('keydown',function(e){ if(e.key==='Escape'){box.classList.remove('hover-open','open'); if(btn)btn.setAttribute('aria-expanded','false')}});
    });
    document.addEventListener('click',function(e){document.querySelectorAll('[data-client-dropdown].open').forEach(function(box){if(!box.contains(e.target) && window.matchMedia('(max-width: 980px)').matches){box.classList.remove('open')}})});
  });
})();
