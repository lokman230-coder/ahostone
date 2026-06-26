(function(){
  function ensureModal(){
    var modal=document.getElementById('aoDomainModal');
    if(modal) return modal;
    modal=document.createElement('div');
    modal.id='aoDomainModal';
    modal.className='ao-domain-modal';
    modal.innerHTML='<div class="ao-domain-backdrop" data-close="1"></div><div class="ao-domain-dialog"><button class="ao-domain-close" data-close="1">×</button><h2 id="aoDomainModalTitle">Domain Sorgu</h2><div id="aoDomainModalBody"><div class="ao-loading">Yükleniyor...</div></div></div>';
    document.body.appendChild(modal);
    modal.addEventListener('click',function(e){ if(e.target.dataset.close){ modal.classList.remove('open'); } });
    document.addEventListener('keydown',function(e){ if(e.key==='Escape') modal.classList.remove('open'); });
    return modal;
  }
  function domainFrom(button){
    var wrap=button.closest('[data-domain-widget]') || document;
    var input=wrap.querySelector('[data-domain-input]') || document.querySelector('[data-domain-input]');
    return input ? input.value.trim() : '';
  }
  async function openTool(tool, domain){
    var modal=ensureModal();
    var title=document.getElementById('aoDomainModalTitle');
    var body=document.getElementById('aoDomainModalBody');
    title.textContent='Domain Sorgu';
    body.innerHTML='<div class="ao-loading">Sorgulanıyor...</div>';
    modal.classList.add('open');
    try{
      var base=(window.AHOST_BASE_URL || (window.location.origin + (window.location.pathname.split('/index.php')[0]||''))).replace(/\/$/,'');
      var res=await fetch(base + '/api/domain-tool?tool='+encodeURIComponent(tool)+'&domain='+encodeURIComponent(domain), {headers:{'Accept':'application/json'}});
      var data=await res.json();
      title.textContent=data.title || 'Domain Sorgu';
      body.innerHTML=data.html || '<div class="ao-modal-error">Sonuç alınamadı.</div>';
      body.querySelectorAll('[data-dns-filter]').forEach(function(btn){
        btn.addEventListener('click',function(){
          var f=btn.dataset.dnsFilter;
          body.querySelectorAll('[data-dns-filter]').forEach(function(b){b.classList.remove('active')});
          btn.classList.add('active');
          body.querySelectorAll('[data-record-type]').forEach(function(row){ row.style.display=(f==='ALL'||row.dataset.recordType===f)?'':'none'; });
        });
      });
    }catch(e){
      body.innerHTML='<div class="ao-modal-error">Sorgu tamamlanamadı. API route veya bağlantı kontrol edilmeli.</div>';
    }
  }

  async function searchDomain(domain, wrap){
    var target=(wrap && wrap.querySelector('[data-domain-search-result]')) || document.querySelector('[data-domain-search-result]');
    if(target) target.innerHTML='<div class="ao-loading">Domain sorgulanıyor...</div>';
    try{
      var base=(window.AHOST_BASE_URL || (window.location.origin + (window.location.pathname.split('/index.php')[0]||''))).replace(/\/$/,'');
      var res=await fetch(base + '/api/domain-search?domain='+encodeURIComponent(domain), {headers:{'Accept':'application/json'}});
      var data=await res.json();
      var html=data.html || ('<div class="ao-search-result"><b>'+domain+'</b><span>'+ (data.message||'Sonuç alınamadı.') +'</span></div>');
      if(target) target.innerHTML=html;
      return data;
    }catch(e){ if(target) target.innerHTML='<div class="ao-modal-error">Domain sorgusu tamamlanamadı.</div>'; return null; }
  }

  document.addEventListener('click',function(e){
    var searchBtn=e.target.closest('[data-domain-search]');
    if(searchBtn){
      e.preventDefault();
      var wrap=searchBtn.closest('[data-domain-widget]') || document;
      var sd=domainFrom(searchBtn);
      if(!sd){ var m=ensureModal(); m.classList.add('open'); document.getElementById('aoDomainModalTitle').textContent='Domain gerekli'; document.getElementById('aoDomainModalBody').innerHTML='<div class="ao-modal-error">Lütfen önce domain adını yazın.</div>'; return; }
      searchDomain(sd, wrap); return;
    }
    var btn=e.target.closest('[data-domain-tool]');
    if(!btn) return;
    e.preventDefault();
    var domain=domainFrom(btn);
    if(!domain){
      var modal=ensureModal(); modal.classList.add('open');
      document.getElementById('aoDomainModalTitle').textContent='Domain gerekli';
      document.getElementById('aoDomainModalBody').innerHTML='<div class="ao-modal-error">Lütfen önce domain adını yazın.</div>';
      return;
    }
    openTool(btn.dataset.domainTool, domain);
  });
})();
