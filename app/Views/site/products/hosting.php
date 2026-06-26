<?php
$service = [
 'kicker'=>'Hosting Center',
 'title'=>'Web hosting, WordPress hosting ve reseller paketlerini tek panelden yönetin.',
 'summary'=>'Ahost One; hosting satışı, müşteri paneli, fatura, destek, bildirim ve otomasyon süreçlerini modern SaaS deneyimiyle birleştirir.',
 'cards'=>[
   ['icon'=>'🖥','title'=>'Web Hosting','text'=>'Başlangıç, profesyonel ve kurumsal hosting paketlerini paket/fiyat/opsiyon mantığıyla sunun.','href'=>'client/register','action'=>'Başla'],
   ['icon'=>'🚀','title'=>'WordPress Hosting','text'=>'WordPress odaklı hızlı kurulum, SSL, yedekleme ve destek akışını tek yerden yönetin.','href'=>'urunler','action'=>'Paketleri Gör'],
   ['icon'=>'🧰','title'=>'Reseller / Özel Paket','text'=>'Ajanslar ve özel projeler için bayi, özel kaynak ve teklif odaklı paket akışı oluşturun.','href'=>'teklif','action'=>'Teklif Al'],
 ],
 'features'=>[['99.9%','Uptime hedefi'],['7/24','Destek akışı'],['SSL','Ücretsiz sertifika'],['AI','Paket önerisi']]
];
require __DIR__.'/_service-page.php';
