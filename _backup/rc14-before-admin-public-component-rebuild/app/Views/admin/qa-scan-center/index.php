<?php
require_once __DIR__ . '/../../../Services/QAScanCenterService.php';
require_once __DIR__ . '/../../../Services/PHPScreenshotBridge.php';
$reports = QAScanCenterService::reports();
$latest = $latestReport ?? QAScanCenterService::latest();
$summary = $latest['summary'] ?? ['score'=>0,'pass'=>0,'warning'=>0,'error'=>0,'broken_links'=>0,'js_errors'=>0,'duration'=>'--:--','visual_pages'=>0,'desktop_screenshots'=>0,'mobile_screenshots'=>0,'real_screenshots'=>0,'fallback_screenshots'=>0,'routes'=>[],'system_rows'=>[]];
$systemRows = $summary['system_rows'] ?? [];
$routes = $summary['routes'] ?? [];
$scanId = $latest['id'] ?? '';
$engineStatus = PHPScreenshotBridge::status();
$cfg = PHPScreenshotBridge::config();
$getSetting = function($key, $default='') { return function_exists('admin_setting') ? admin_setting($key, $default) : $default; };
?>
<div class="rc8-qa-page">
  <div class="rc8-hero">
    <div class="rc8-hero-icon">🛡</div>
    <div>
      <h2>QA &amp; Scan Center Pro</h2>
      <p>Görsel tarama, sistem taraması, mobil/masaüstü ekran görüntüsü ve rapor paketleri tek merkezde. RC9 ile PHP Screenshot Bridge eklendi.</p>
    </div>
    <div class="rc8-actions">
      <a class="rc8-btn soft" href="<?= url('admin/qa-scan-center') ?>">🕘 Tarama Geçmişi</a>
      <form method="post" action="<?= url('admin/qa-scan-center/run') ?>"><?= csrf_field() ?><button class="rc8-btn primary">▶ Tam Tarama Başlat</button></form>
    </div>
  </div>

  <div class="rc8-tabs" data-ao-tabs>
    <button class="active" data-tab="overview">⌂ Genel Bakış</button>
    <button data-tab="visual">🖼 Görsel Tarama</button>
    <button data-tab="system">🧪 Sistem Taraması</button>
    <button data-tab="database">▣ Veritabanı</button>
    <button data-tab="routes">🔗 Route &amp; Link</button>
    <button data-tab="modules">⚙ Modüller</button>
    <button data-tab="api">🔌 API Kontrolü</button>
    <button data-tab="engine">📸 Screenshot Motoru</button>
    <button data-tab="reports">📄 Raporlar</button>
  </div>

  <section class="rc8-panel active" data-panel="overview">
    <div class="rc8-metrics">
      <div class="rc8-metric score"><small>Genel Skor</small><strong><?= (int)($summary['score'] ?? 0) ?>/100</strong><div class="rc8-bar"><span style="width:<?= (int)($summary['score'] ?? 0) ?>%"></span></div></div>
      <div class="rc8-metric"><small>PASS</small><strong><?= (int)($summary['pass'] ?? 0) ?></strong><span class="rc8-dot pass">✓</span></div>
      <div class="rc8-metric"><small>Warning</small><strong><?= (int)($summary['warning'] ?? 0) ?></strong><span class="rc8-dot warning">!</span></div>
      <div class="rc8-metric"><small>Error</small><strong><?= (int)($summary['error'] ?? 0) ?></strong><span class="rc8-dot error">×</span></div>
      <div class="rc8-metric"><small>Gerçek Görsel</small><strong><?= (int)($summary['real_screenshots'] ?? 0) ?></strong><span class="rc8-dot pass">PNG</span></div>
      <div class="rc8-metric"><small>Fallback</small><strong><?= (int)($summary['fallback_screenshots'] ?? 0) ?></strong><span class="rc8-dot warning">SVG</span></div>
      <div class="rc8-metric"><small>Tarama Süresi</small><strong><?= e($summary['duration'] ?? '--:--') ?></strong><span class="rc8-dot info">⏱</span></div>
    </div>

    <div class="rc8-grid three">
      <div class="rc8-card"><h3>Tarama Kapsamı</h3><div class="rc8-donut"><strong><?= (int)($summary['visual_pages'] ?? 0) ?></strong><span>Sayfa / Kontrol</span></div><ul class="rc8-list"><li><span>Site Ön Yüz</span><b><?= count(array_filter($routes, fn($r)=>($r['area']??'')==='Site')) ?></b></li><li><span>Admin Panel</span><b><?= count(array_filter($routes, fn($r)=>($r['area']??'')==='Admin')) ?></b></li><li><span>Müşteri Paneli</span><b><?= count(array_filter($routes, fn($r)=>($r['area']??'')==='Müşteri')) ?></b></li><li><span>Sistem Kontrolleri</span><b><?= count($systemRows) ?></b></li></ul></div>
      <div class="rc8-card"><h3>PHP Screenshot Bridge</h3><table class="rc8-table compact"><tr><td>Seçili Motor</td><td><b><?= e($engineStatus['configured_engine'] ?? 'auto') ?></b></td></tr><tr><td>Önerilen</td><td><b><?= e($engineStatus['recommended_engine'] ?? 'basic') ?></b></td></tr><tr><td>Local Chrome</td><td><?= !empty($engineStatus['local_chrome_path']) ? e($engineStatus['local_chrome_path']) : '<span class="rc8-status warning">Yok</span>' ?></td></tr><tr><td>Remote API</td><td><?= !empty($engineStatus['remote_configured']) ? '<span class="rc8-status pass">Ayarlı</span>' : '<span class="rc8-status warning">Ayarlı değil</span>' ?></td></tr></table><button class="rc8-outline" data-jump="engine">Motor Ayarlarına Git</button></div>
      <div class="rc8-card"><h3>Rapor &amp; İndir</h3><p class="rc8-muted">Tam rapor; sistem sonucu, HTML rapor, ekran görüntüleri, JSON ve loglarla birlikte ZIP olarak indirilir.</p><?php if($latest): ?><a class="rc8-download zip" href="<?= url('admin/qa-scan-center/download?report='.urlencode($scanId).'&file=zip') ?>">⬇ ZIP Rapor İndir<small>Tüm rapor, ekran görüntüleri ve loglar</small></a><a class="rc8-download" href="<?= url('admin/qa-scan-center/download?report='.urlencode($scanId).'&file=html') ?>">📄 HTML Rapor<small>Tarayıcıda görüntüle/indir</small></a><a class="rc8-download" href="<?= url('admin/qa-scan-center/download?report='.urlencode($scanId).'&file=pdf') ?>">📕 PDF Rapor<small>PDF formatında indir</small></a><?php else: ?><p class="rc8-muted">Henüz rapor yok. Önce Tam Tarama Başlat.</p><?php endif; ?></div>
    </div>

    <div class="rc8-grid three">
      <div class="rc8-card"><h3>Son Bulunan Problemler</h3><div class="rc8-issues"><?php $issues=array_filter(array_merge($routes,$systemRows), fn($r)=>in_array($r['status']??'', ['warning','error','fail','demo'], true)); foreach(array_slice($issues,0,5) as $r): ?><div><span class="rc8-status <?= e($r['status']??'warning') ?>"><?= e(strtoupper($r['status']??'WARN')) ?></span><b><?= e($r['label'] ?? $r['name'] ?? 'Kontrol') ?></b><small><?= e($r['notes'] ?? $r['detail'] ?? '') ?></small></div><?php endforeach; if(!$issues): ?><p class="rc8-muted">Son taramada kritik problem bulunmadı.</p><?php endif; ?></div></div>
      <div class="rc8-card"><h3>Ekran Görüntüleri</h3><div class="rc8-shot-summary"><div><span>🖥</span><b><?= (int)($summary['desktop_screenshots'] ?? 0) ?></b><small>Masaüstü</small></div><div><span>📱</span><b><?= (int)($summary['mobile_screenshots'] ?? 0) ?></b><small>Mobil</small></div></div><?php if($routes): ?><div class="rc8-preview-strip"><?php foreach(array_slice($routes,0,3) as $r): ?><img src="<?= url('storage/reports/qa-scans/'.e($scanId).'/'.e($r['desktop'])) ?>" alt=""><?php endforeach; ?></div><?php endif; ?><button class="rc8-outline" data-jump="visual">Tüm Ekran Görüntülerini Görüntüle</button></div>
      <div class="rc8-card"><h3>Tarama Özeti</h3><table class="rc8-table compact"><tr><td>Son Tarama</td><td><?= e($summary['generated_at'] ?? '-') ?></td></tr><tr><td>Toplam Kontrol</td><td><?= (int)($summary['total_checks'] ?? 0) ?></td></tr><tr><td>Alınan Ekran Görüntüsü</td><td><?= (int)(($summary['desktop_screenshots'] ?? 0)+($summary['mobile_screenshots'] ?? 0)) ?></td></tr><tr><td>Rapor ID</td><td><?= e($scanId ?: '-') ?></td></tr></table></div>
    </div>
  </section>

  <section class="rc8-panel" data-panel="visual"><div class="rc8-card"><h3>Görsel Tarama Sonuçları</h3><p class="rc8-muted">PNG olanlar gerçek tarayıcı ekran görüntüsüdür. SVG olanlar motor bulunamadığında üretilen fallback kaydıdır.</p><div class="rc8-screenshot-grid"><?php foreach($routes as $r): ?><div class="rc8-shot"><div class="rc8-shot-head"><b><?= e($r['label']) ?></b><span class="rc8-status <?= e($r['status']) ?>"><?= e($r['score']) ?>/100</span></div><div class="rc8-shot-images"><img src="<?= url('storage/reports/qa-scans/'.e($scanId).'/'.e($r['desktop'])) ?>" alt="desktop"><img src="<?= url('storage/reports/qa-scans/'.e($scanId).'/'.e($r['mobile'])) ?>" alt="mobile"></div><p><?= e($r['notes']) ?></p><small><?= !empty($r['desktop_real'])?'Desktop gerçek PNG':'Desktop fallback' ?> · <?= !empty($r['mobile_real'])?'Mobil gerçek PNG':'Mobil fallback' ?> · <?= e($r['desktop_engine'] ?? '') ?>/<?= e($r['mobile_engine'] ?? '') ?></small></div><?php endforeach; if(!$routes): ?><p class="rc8-muted">Henüz görsel tarama sonucu yok.</p><?php endif; ?></div></div></section>

  <section class="rc8-panel" data-panel="system"><div class="rc8-card"><h3>Sistem Taraması</h3><table class="rc8-table"><thead><tr><th>Kategori</th><th>Kontrol</th><th>Durum</th><th>Öncelik</th><th>Detay</th><th>Öneri</th></tr></thead><tbody><?php foreach($systemRows as $r): ?><tr><td><?= e($r['category'] ?? '') ?></td><td><b><?= e($r['name'] ?? '') ?></b></td><td><span class="rc8-status <?= e($r['status'] ?? 'pass') ?>"><?= e(strtoupper($r['status'] ?? 'PASS')) ?></span></td><td><?= e($r['priority'] ?? '') ?></td><td><?= e($r['detail'] ?? '') ?></td><td><?= e($r['recommendation'] ?? '') ?></td></tr><?php endforeach; if(!$systemRows): ?><tr><td colspan="6" class="rc8-muted">Henüz sistem taraması yok.</td></tr><?php endif; ?></tbody></table></div></section>

  <section class="rc8-panel" data-panel="database"><div class="rc8-card"><h3>Veritabanı Kontrolleri</h3><table class="rc8-table"><tbody><?php foreach(array_filter($systemRows, fn($r)=>($r['category']??'')==='Veritabanı') as $r): ?><tr><td><b><?= e($r['name']) ?></b></td><td><span class="rc8-status <?= e($r['status']) ?>"><?= e(strtoupper($r['status'])) ?></span></td><td><?= e($r['detail']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
  <section class="rc8-panel" data-panel="routes"><div class="rc8-card"><h3>Route &amp; Link Kontrolleri</h3><table class="rc8-table"><thead><tr><th>Alan</th><th>Sayfa</th><th>URL</th><th>Durum</th><th>Not</th></tr></thead><tbody><?php foreach($routes as $r): ?><tr><td><?= e($r['area']) ?></td><td><b><?= e($r['label']) ?></b></td><td><code><?= e($r['url']) ?></code></td><td><span class="rc8-status <?= e($r['status']) ?>"><?= e(strtoupper($r['status'])) ?></span></td><td><?= e($r['notes']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
  <section class="rc8-panel" data-panel="modules"><div class="rc8-card"><h3>Modül Kontrolleri</h3><p class="rc8-muted">Modül merkezi, Build Center, API Entegrasyonları, Yardım Kılavuzu, Güncelleme Merkezi ve admin sekme yapısı burada değerlendirilir.</p></div></section>
  <section class="rc8-panel" data-panel="api"><div class="rc8-card"><h3>API Kontrolü</h3><p class="rc8-muted">DomainNameAPI, SMS, sunucu paneli, ödeme, yapay zeka ve mail/SMTP bağlantıları tek API Entegrasyonları yapısına göre denetlenir.</p></div></section>

  <section class="rc8-panel" data-panel="engine">
    <div class="rc8-grid two">
      <div class="rc8-card"><h3>Motor Durumu</h3><table class="rc8-table compact"><tr><td>Seçili Motor</td><td><b><?= e($engineStatus['configured_engine'] ?? 'auto') ?></b></td></tr><tr><td>Önerilen Motor</td><td><b><?= e($engineStatus['recommended_engine'] ?? 'basic') ?></b></td></tr><tr><td>PHP exec</td><td><?= !empty($engineStatus['exec_enabled']) ? '<span class="rc8-status pass">Açık</span>' : '<span class="rc8-status warning">Kapalı</span>' ?></td></tr><tr><td>Chrome/Chromium</td><td><?= !empty($engineStatus['local_chrome_path']) ? e($engineStatus['local_chrome_path']) : '<span class="rc8-status warning">Bulunamadı</span>' ?></td></tr><tr><td>Remote API</td><td><?= !empty($engineStatus['remote_configured']) ? '<span class="rc8-status pass">Ayarlı</span>' : '<span class="rc8-status warning">Ayarlı değil</span>' ?></td></tr><tr><td>Desktop</td><td><?= e($engineStatus['desktop'] ?? '-') ?></td></tr><tr><td>Mobil</td><td><?= e($engineStatus['mobile'] ?? '-') ?></td></tr></table></div>
      <div class="rc8-card"><h3>Screenshot Motoru Ayarları</h3><form method="post" action="<?= url('admin/qa-scan-center/settings') ?>"><?= csrf_field() ?><div class="ao-form-grid"><label>Motor<select name="settings[qa_screenshot_engine]"><option value="auto" <?= $getSetting('qa_screenshot_engine','auto')==='auto'?'selected':'' ?>>Otomatik</option><option value="local_chrome" <?= $getSetting('qa_screenshot_engine','auto')==='local_chrome'?'selected':'' ?>>Local Chrome / Chromium</option><option value="remote_api" <?= $getSetting('qa_screenshot_engine','auto')==='remote_api'?'selected':'' ?>>Remote Screenshot API</option><option value="basic" <?= $getSetting('qa_screenshot_engine','auto')==='basic'?'selected':'' ?>>Basic / Screenshotsız Fallback</option></select></label><label>Chrome Path<input name="settings[qa_screenshot_chrome_path]" value="<?= e($getSetting('qa_screenshot_chrome_path','')) ?>" placeholder="/usr/bin/google-chrome"></label><label>Remote API Endpoint<input name="settings[qa_screenshot_remote_endpoint]" value="<?= e($getSetting('qa_screenshot_remote_endpoint','')) ?>" placeholder="https://screenshot-api.example.com/capture"></label><label>Remote API Token<input type="password" name="settings[qa_screenshot_remote_token]" value="<?= e($getSetting('qa_screenshot_remote_token','')) ?>"></label><label>Bekleme Süresi ms<input name="settings[qa_screenshot_wait_ms]" value="<?= e($getSetting('qa_screenshot_wait_ms','2500')) ?>"></label><label>Timeout sn<input name="settings[qa_screenshot_timeout]" value="<?= e($getSetting('qa_screenshot_timeout','12')) ?>"></label><label>Desktop Genişlik<input name="settings[qa_screenshot_desktop_width]" value="<?= e($getSetting('qa_screenshot_desktop_width','1440')) ?>"></label><label>Desktop Yükseklik<input name="settings[qa_screenshot_desktop_height]" value="<?= e($getSetting('qa_screenshot_desktop_height','1200')) ?>"></label><label>Mobil Genişlik<input name="settings[qa_screenshot_mobile_width]" value="<?= e($getSetting('qa_screenshot_mobile_width','390')) ?>"></label><label>Mobil Yükseklik<input name="settings[qa_screenshot_mobile_height]" value="<?= e($getSetting('qa_screenshot_mobile_height','844')) ?>"></label></div><button class="rc8-btn primary" style="margin-top:12px">Ayarları Kaydet</button></form><p class="rc8-muted">Playwright zorunlu değildir. Local Chrome varsa PHP bridge gerçek PNG üretir. Yoksa Remote API denenir. İkisi de yoksa sistem SVG fallback ve sistem raporu üretir.</p></div>
    </div>
  </section>

  <section class="rc8-panel" data-panel="reports"><div class="rc8-card"><h3>Tarama Geçmişi ve Raporlar</h3><table class="rc8-table"><thead><tr><th>Rapor</th><th>Skor</th><th>Görseller</th><th>HTML</th><th>PDF</th><th>ZIP</th><th>Durum</th></tr></thead><tbody><?php foreach($reports as $r): ?><tr><td><?= e($r['id']) ?></td><td><?= (int)($r['summary']['score'] ?? 0) ?>/100</td><td><?= (int)($r['summary']['real_screenshots'] ?? 0) ?> gerçek / <?= (int)($r['summary']['fallback_screenshots'] ?? 0) ?> fallback</td><td><?= $r['html']?'<a class="rc8-link" href="'.url('admin/qa-scan-center/download?report='.urlencode($r['id']).'&file=html').'">Görüntüle</a>':'-' ?></td><td><?= $r['pdf']?'<a class="rc8-link" href="'.url('admin/qa-scan-center/download?report='.urlencode($r['id']).'&file=pdf').'">İndir</a>':'-' ?></td><td><?= $r['zip']?'<a class="rc8-link" href="'.url('admin/qa-scan-center/download?report='.urlencode($r['id']).'&file=zip').'">ZIP İndir</a>':'-' ?></td><td><span class="rc8-status pass">Kaydedildi</span></td></tr><?php endforeach; if(!$reports): ?><tr><td colspan="7" class="rc8-muted">Henüz rapor yok.</td></tr><?php endif; ?></tbody></table></div></section>
</div>
