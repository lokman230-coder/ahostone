<?php
$service = [
 'kicker'=>'AI MobileBuilder',
 'title'=>'Kod bilmeden mobil uygulama tasarlayın, APK/AAB build sürecini tek merkezden yönetin.',
 'summary'=>'MobileBuilder; uygulama ekranları, ikon, splash, tema, önizleme, build kuyruğu ve AI destekli uygulama tasarımını birleştirir.',
 'cards'=>[
   ['icon'=>'📱','title'=>'AI Uygulama Tasarımı','text'=>'Uygulama fikrine göre ekran akışı, alt menü, onboarding, metin ve renk önerileri üretir.','href'=>'mobilebuilder/create-demo','action'=>'Demo Oluştur'],
   ['icon'=>'🧩','title'=>'Şablonlar','text'=>'Restoran, randevu, eğitim, e-ticaret, radyo ve kurumsal uygulama şablonları.','href'=>'mobilebuilder/preview-public','action'=>'Önizle'],
   ['icon'=>'⚙️','title'=>'Build Center','text'=>'APK/AAB üretimi, build logları, hata analizi ve indirme süreçleri tek merkezde.','href'=>'mobilebuilder/build','action'=>'Build Bilgisi'],
 ],
 'features'=>[['APK/AAB','Build kuyruğu'],['AI','Ekran akışı'],['PWA','Web app'],['Log','Hata analizi']]
];
require __DIR__.'/_service-page.php';
