(function(){
  function insertHtml(html){ document.execCommand('insertHTML', false, html); }
  const blocks={
    button:'<p><a class="ao-product-button" href="#siparis">Hemen Sipariş Ver</a></p>',
    features:'<div class="ao-product-feature-grid"><div><strong>Yüksek Performans</strong><p>SSD altyapı ve optimize sistem.</p></div><div><strong>Güvenli Altyapı</strong><p>SSL ve güvenlik katmanı.</p></div><div><strong>Uzman Destek</strong><p>Profesyonel destek ekibi.</p></div></div>',
    faq:'<div class="ao-product-faq"><h3>Sık Sorulan Sorular</h3><p><strong>Kurulum ne kadar sürer?</strong><br>Ödeme sonrası otomatik veya manuel teslim edilir.</p></div>',
    table:'<table><thead><tr><th>Özellik</th><th>Değer</th></tr></thead><tbody><tr><td>Disk</td><td>10 GB SSD</td></tr><tr><td>SSL</td><td>Ücretsiz</td></tr></tbody></table>',
    notice:'<div class="ao-product-notice"><strong>Bilgi:</strong> Bu paket büyüyen projeler için önerilir.</div>',
    media:'<figure><img src="/public/assets/img/placeholder-product.svg" alt="Ürün görseli"><figcaption>Ürün görseli açıklaması</figcaption></figure>',
    pricing:'<section class="ao-product-pricing-block"><h3>Öne Çıkan Paket</h3><p>Yüksek performans, güvenli altyapı ve uzman destek.</p><p><a class="ao-product-button" href="#siparis">Hemen Sipariş Ver</a></p></section>',
    hero:'<section><h2>Profesyonel Ürün Sayfası</h2><p>Satış odaklı açıklama, özellikler ve güçlü çağrı butonu.</p><p><a class="ao-product-button" href="#siparis">Hemen Başla</a></p></section>',
    compare:'<table><thead><tr><th>Özellik</th><th>Başlangıç</th><th>Kurumsal</th></tr></thead><tbody><tr><td>Disk</td><td>5 GB</td><td>50 GB</td></tr><tr><td>Destek</td><td>Standart</td><td>Öncelikli</td></tr></tbody></table>',
    steps:'<ol><li>Sipariş oluşturulur.</li><li>Ödeme kontrol edilir.</li><li>Hizmet otomatik veya manuel teslim edilir.</li></ol>'
  };
  document.addEventListener('click',function(e){
    const studio=e.target.closest('[data-content-studio]'); if(!studio) return;
    const visual=studio.querySelector('[data-editor-visual]'); const html=studio.querySelector('[data-editor-html]');
    const btn=e.target.closest('button'); if(!btn) return;
    if(btn.dataset.mode){
      if(btn.dataset.mode==='html'){ html.value=visual.innerHTML.trim(); studio.classList.add('is-html'); }
      else { visual.innerHTML=html.value; studio.classList.remove('is-html'); }
      studio.querySelectorAll('[data-mode]').forEach(b=>b.classList.toggle('active',b===btn)); return;
    }
    if(btn.dataset.cmd){ visual.focus(); document.execCommand(btn.dataset.cmd,false,btn.dataset.value||null); html.value=visual.innerHTML.trim(); return; }
    if(btn.dataset.action==='link'){ const u=prompt('Bağlantı URL'); if(u){ visual.focus(); document.execCommand('createLink',false,u); html.value=visual.innerHTML.trim(); } return; }
    if(btn.dataset.action==='image'){ const u=prompt('Görsel URL'); if(u){ visual.focus(); insertHtml('<figure><img src=\"'+u.replace(/\"/g,'')+'\" alt=\"Ürün görseli\"><figcaption>Görsel açıklaması</figcaption></figure>'); html.value=visual.innerHTML.trim(); } return; }
    if(btn.dataset.block){ visual.focus(); insertHtml(blocks[btn.dataset.block]||''); html.value=visual.innerHTML.trim(); return; }
  });
  document.addEventListener('input',function(e){
    const studio=e.target.closest('[data-content-studio]'); if(!studio) return;
    const visual=studio.querySelector('[data-editor-visual]'); const html=studio.querySelector('[data-editor-html]');
    if(e.target.matches('[data-editor-visual]')) html.value=visual.innerHTML.trim();
  });
  document.addEventListener('submit',function(e){
    e.target.querySelectorAll('[data-content-studio]').forEach(function(studio){
      const visual=studio.querySelector('[data-editor-visual]'); const html=studio.querySelector('[data-editor-html]');
      if(!studio.classList.contains('is-html')) html.value=visual.innerHTML.trim();
    });
  },true);
})();
