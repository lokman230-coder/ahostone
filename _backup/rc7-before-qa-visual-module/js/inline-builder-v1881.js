(function(){
  function qs(s,ctx){return (ctx||document).querySelector(s)}
  function qsa(s,ctx){return Array.prototype.slice.call((ctx||document).querySelectorAll(s))}
  function addBlockMarkers(){
    qsa('.u2-card,.customer-panel-card,.site-hero,.e-card,.ao-content > .u2-card,.ao-content > .customer-panel-card').forEach(function(el,i){
      if(!el.hasAttribute('data-builder-block')) el.setAttribute('data-builder-block','block-'+(i+1));
      el.setAttribute('contenteditable','false');
    });
  }
  function enable(){
    document.body.classList.add('ao-inline-editing');
    addBlockMarkers();
    if(!qs('.ao-inline-toolbar')){
      var toolbar=document.createElement('div');
      toolbar.className='ao-inline-toolbar';
      toolbar.innerHTML='<button class="primary" type="button" data-ao-save>Kaydet</button><button type="button" data-ao-text>Yazıları Düzenle</button><button type="button" data-ao-reset>Vazgeç</button><a href="#" data-ao-admin>Gelişmiş Builder</a>';
      document.body.appendChild(toolbar);
      var panel=document.createElement('div');
      panel.className='ao-inline-panel';
      panel.innerHTML='<h3>Sayfa Üzeri Düzenleme</h3><p>Bu mod admin paneline gitmeden bulunduğun sayfada hızlı düzenleme içindir. Bloklara tıkla, yazı düzenlemeyi aç, değişiklikleri taslak olarak kaydet.</p><label>Görünüm</label><select><option>Desktop</option><option>Tablet</option><option>Mobil</option></select><label>Seçili blok</label><input readonly value="Blok seçilmedi">';
      document.body.appendChild(panel);
      toolbar.querySelector('[data-ao-reset]').onclick=function(){disable()};
      toolbar.querySelector('[data-ao-text]').onclick=function(){
        var on=!document.body.classList.toggle('ao-text-editing');
        qsa('[data-builder-block]').forEach(function(el){el.setAttribute('contenteditable', document.body.classList.contains('ao-text-editing')?'true':'false')});
      };
      toolbar.querySelector('[data-ao-save]').onclick=function(){
        var key='ao_inline_draft_'+location.pathname;
        var data=qsa('[data-builder-block]').map(function(el){return {id:el.getAttribute('data-builder-block'),html:el.innerHTML}});
        try{localStorage.setItem(key,JSON.stringify(data));alert('Taslak bu tarayıcıya kaydedildi. Yayına alma için gelişmiş Builder kullanılabilir.')}catch(e){alert('Taslak kaydedilemedi.');}
      };
      toolbar.querySelector('[data-ao-admin]').onclick=function(e){
        e.preventDefault();
        var target=document.body.getAttribute('data-app')||'site';
        var template=(location.pathname||'/').replace(/^\//,'').replace(/[^a-z0-9\/_-]/gi,'')||'home';
        location.href=(window.AHOST_BASE_URL||'')+'/admin/builder-pro?target='+encodeURIComponent(target)+'&template='+encodeURIComponent(template);
      };
    }
  }
  function disable(){document.body.classList.remove('ao-inline-editing','ao-text-editing');qsa('[data-builder-block]').forEach(function(el){el.setAttribute('contenteditable','false')});}
  document.addEventListener('click',function(e){
    var a=e.target.closest('.ao-inline-edit-start,[data-ao-inline-edit]');
    if(a){e.preventDefault();enable();}
    var block=e.target.closest('[data-builder-block]');
    if(block && document.body.classList.contains('ao-inline-editing')){
      var p=qs('.ao-inline-panel input'); if(p) p.value=block.getAttribute('data-builder-block')||block.className||'Blok';
    }
  });
})();