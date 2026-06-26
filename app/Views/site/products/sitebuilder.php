<?php
$service = [
 'kicker'=>'AI SiteBuilder',
 'title'=>'Yapay zeka destekli SiteBuilder ile dakikalar içinde profesyonel web sitesi oluşturun.',
 'summary'=>'Hazır şablonlar, sürükle-bırak editör, AI içerik/tasarım önerileri, önizleme ve yayınlama akışı tek merkezde.',
 'cards'=>[
   ['icon'=>'🎨','title'=>'AI Tasarım','text'=>'Sektör, firma adı ve hedefe göre sayfa yapısı, renk paleti, metin ve CTA üretir.','href'=>'sitebuilder/create-demo','action'=>'Demo Oluştur'],
   ['icon'=>'🧱','title'=>'Blok Editör','text'=>'Hero, hizmet, fiyat, referans, form, SSS ve iletişim bloklarını kolayca düzenleyin.','href'=>'sitebuilder/preview-public','action'=>'Önizle'],
   ['icon'=>'🚀','title'=>'Yayınlama / Export','text'=>'Yayınlama, ZIP export ve müşteri proje yönetimi paket iznine göre çalışır.','href'=>'sitebuilder/export','action'=>'Export Bilgisi'],
 ],
 'features'=>[['AI','Site tasarımı'],['SEO','Meta önerileri'],['Mobil','Responsive'],['Export','ZIP akışı']]
];
require __DIR__.'/_service-page.php';
