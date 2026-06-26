document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('[data-domain-quote-form]').forEach(function(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const out = document.querySelector(form.getAttribute('data-output'));
      const domain = form.querySelector('[name=domain]')?.value || '';
      const gateway = form.querySelector('[name=gateway]')?.value || 'paytr';
      fetch((window.AHOST_BASE_URL||'') + '/api/domain-quote?domain=' + encodeURIComponent(domain) + '&gateway=' + encodeURIComponent(gateway))
        .then(r=>r.json()).then(j=>{ if(out) out.innerHTML = '<b>'+j.domain+'</b> → '+j.sale_price+' '+j.currency+' / '+j.selected_registrar+'<br><small>Alış: '+j.registrar_cost+' · Kâr: '+j.profit+' · Kart toplamı: '+(j.payment?j.payment.customer_total:'-')+'</small>'; })
        .catch(()=>{ if(out) out.textContent='Fiyat hesaplanamadı.'; });
    });
  });
});
