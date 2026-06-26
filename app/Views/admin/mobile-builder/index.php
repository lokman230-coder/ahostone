<?php
$projects=[]; $recentBuilds=[];
try { $projects = db()->query('SELECT * FROM module_mobilebuilder_projects ORDER BY updated_at DESC LIMIT 12')->fetchAll(); } catch(Throwable $e) {}
try { $recentBuilds = db()->query('SELECT * FROM module_mobilebuilder_builds ORDER BY created_at DESC LIMIT 8')->fetchAll(); } catch(Throwable $e) {}
$stats = ['projects'=>count($projects),'builds'=>table_count('module_mobilebuilder_builds'),'done'=>0,'failed'=>0];
try { $stats['done']=(int)db()->query("SELECT COUNT(*) FROM module_mobilebuilder_builds WHERE status='completed'")->fetchColumn(); } catch(Throwable $e) {}
try { $stats['failed']=(int)db()->query("SELECT COUNT(*) FROM module_mobilebuilder_builds WHERE status='failed'")->fetchColumn(); } catch(Throwable $e) {}
?>
<div class="ao-admin-page-head"><div><span>AI MobileBuilder</span><h1>MobileBuilder Pro</h1><p>Uygulama ekranı, ikon, splash, alt menü, AI tasarım ve APK/AAB build süreçlerini tek merkezden yönetin.</p></div><div class="ao-actions"><a class="ao-btn" href="<?= url('admin/mobile-builder/ai') ?>">🤖 AI ile Oluştur</a><a class="ao-btn ao-btn--primary" href="<?= url('admin/mobile-builder/editor') ?>">Uygulama Tasarla</a></div></div>
<div class="ao-admin-stat-grid">
  <a class="ao-admin-stat" href="<?= url('admin/mobile-builder/editor') ?>"><i>📱</i><small>Projeler</small><strong><?= $stats['projects'] ?></strong><span>Mobil uygulamalar</span></a>
  <a class="ao-admin-stat" href="<?= url('admin/mobile-builder/build-center') ?>"><i>⚙️</i><small>Build</small><strong><?= $stats['builds'] ?></strong><span>APK/AAB işlemi</span></a>
  <a class="ao-admin-stat" href="<?= url('admin/mobile-builder/build-log') ?>"><i>✅</i><small>Başarılı</small><strong><?= $stats['done'] ?></strong><span>Tamamlanan build</span></a>
  <a class="ao-admin-stat" href="<?= url('admin/mobile-builder/build-log') ?>"><i>⚠️</i><small>Hatalı</small><strong><?= $stats['failed'] ?></strong><span>İncelenecek log</span></a>
</div>
<div class="ao-admin-grid two">
  <section class="ao-admin-card"><div class="ao-card-head"><h2>Projeler</h2><a class="ao-btn" href="<?= url('admin/mobile-builder/editor') ?>">+ Yeni Proje</a></div><?php if(!$projects): ?><div class="ao-empty-premium"><b>Henüz mobil proje yok.</b><p>AI ile restoran, randevu, eğitim, e-ticaret veya radyo uygulaması tasarlayın.</p><a class="ao-btn" href="<?= url('admin/mobile-builder/ai') ?>">AI ile Başla</a></div><?php else: ?><table class="ao-table"><thead><tr><th>Proje</th><th>Şablon</th><th>Durum</th><th>İşlem</th></tr></thead><tbody><?php foreach($projects as $p): ?><tr><td><?= e($p['name'] ?? 'Uygulama') ?></td><td><?= e($p['template'] ?? '-') ?></td><td><span class="ao-badge"><?= e($p['status'] ?? 'draft') ?></span></td><td><a class="ao-mini-btn" href="<?= url('admin/mobile-builder/editor?id='.(int)$p['id']) ?>">Düzenle</a></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section>
  <section class="ao-admin-card"><div class="ao-card-head"><h2>Build Merkezi</h2><a class="ao-btn" href="<?= url('admin/mobile-builder/build-center') ?>">Aç</a></div><div class="ao-feature-list"><div>📦 APK / AAB çıktı</div><div>🧾 Build logları</div><div>🤖 AI hata analizi</div><div>⚙️ SDK/JDK/Gradle kontrolü</div></div></section>
</div>
