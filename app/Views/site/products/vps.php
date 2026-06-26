<?php
$service = [
 'kicker'=>'VPS & Sunucu Merkezi',
 'title'=>'Performans odaklı VPS, dedicated ve yönetilen sunucu hizmetlerini tek panelden yönetin.',
 'summary'=>'Sunucu paketleri, lokasyon, kaynak kullanımı, lisans ve destek süreçleri Ahost One otomasyonuna bağlı çalışır.',
 'cards'=>[
   ['icon'=>'☁️','title'=>'VPS / VDS','text'=>'CPU, RAM, disk ve trafik bilgileriyle ölçeklenebilir sanal sunucu paketleri sunun.','href'=>'client/register','action'=>'Başla'],
   ['icon'=>'🏢','title'=>'Dedicated Sunucu','text'=>'Yüksek performanslı özel sunucu tekliflerini yönetilen hizmet akışıyla teslim edin.','href'=>'teklif','action'=>'Teklif Al'],
   ['icon'=>'🛡','title'=>'Yönetilen Sunucu','text'=>'Bakım, güvenlik, yedekleme ve panel yönetimini premium destek ile sunun.','href'=>'dijital-hizmetler','action'=>'Detay Al'],
 ],
 'features'=>[['CPU/RAM','Kaynak takibi'],['Lokasyon','Çoklu bölge'],['Panel','cPanel/Plesk'],['Otomasyon','Provisioning']]
];
require __DIR__.'/_service-page.php';
