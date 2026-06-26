<?php
$projects = [];
$pages = [];
try { $projects = db()->query('SELECT * FROM sitebuilder_projects ORDER BY updated_at DESC LIMIT 8')->fetchAll(); } catch(Throwable $e) {}
try { $pages = db()->query('SELECT p.*, pr.name AS project_name FROM sitebuilder_pages p LEFT JOIN sitebuilder_projects pr ON pr.id=p.project_id ORDER BY p.updated_at DESC, p.id DESC LIMIT 8')->fetchAll(); } catch(Throwable $e) {}
?>
<div class="ao-admin-page-head"><div><span>AI SiteBuilder</span><h1>SiteBuilder Pro</h1><p>Canlı sürükle-bırak sayfa oluşturma, AI tasarım, tema entegrasyonu, form/popup builder ve ZIP export merkezi.</p></div><div class="ao-actions"><a class="ao-btn" href="<?= url('admin/site-builder/ai-design') ?>">🤖 AI Tasarla</a><a class="ao-btn ao-btn--primary" href="<?= url('admin/site-builder/pages') ?>">Sayfalar</a></div></div>
<div class="ao-admin-stat-grid">
  <a class="ao-admin-stat" href="<?= url('admin/site-builder/pages') ?>"><i>📄</i><small>Sayfalar</small><strong><?= table_count('sitebuilder_pages') ?></strong><span>Web sayfaları</span></a>
  <a class="ao-admin-stat" href="<?= url('admin/site-builder') ?>"><i>🎨</i><small>Projeler</small><strong><?= table_count('sitebuilder_projects') ?></strong><span>Müşteri projeleri</span></a>
  <a class="ao-admin-stat" href="<?= url('admin/site-builder/exports') ?>"><i>📦</i><small>Export</small><strong><?= table_count('sitebuilder_exports') ?></strong><span>ZIP çıktıları</span></a>
</div>
<div class="ao-admin-grid two">
  <section class="ao-admin-card"><div class="ao-card-head"><h2>Projeler</h2><a class="ao-btn" href="<?= url('admin/site-builder/editor') ?>">+ Yeni</a></div><?php if(!$projects): ?><div class="ao-empty-premium">Henüz proje yok. AI ile demo site oluşturabilir veya yeni sayfa ekleyebilirsiniz.</div><?php else: ?><div class="ao-list-cards"><?php foreach($projects as $p): ?><a href="<?= url('admin/site-builder/pages?project='.(int)$p['id']) ?>"><b><?= e($p['name'] ?? 'Proje') ?></b><span><?= e($p['status'] ?? 'active') ?></span></a><?php endforeach; ?></div><?php endif; ?></section>
  <section class="ao-admin-card"><div class="ao-card-head"><h2>Son Sayfalar</h2><a class="ao-btn" href="<?= url('admin/site-builder/pages') ?>">Tümü</a></div><?php if(!$pages): ?><div class="ao-empty-premium">Henüz sayfa yok.</div><?php else: ?><table class="ao-table"><thead><tr><th>Sayfa</th><th>Proje</th><th>İşlem</th></tr></thead><tbody><?php foreach($pages as $p): ?><tr><td><?= e($p['title'] ?? 'Sayfa') ?></td><td><?= e($p['project_name'] ?? '-') ?></td><td><a class="ao-mini-btn" href="<?= url('admin/site-builder/editor?id='.(int)$p['id']) ?>">Düzenle</a></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section>
</div>
<section class="ao-admin-card"><h2>Live Builder Özellikleri</h2><div class="ao-feature-list"><div>🎨 Sürükle-bırak öğe ekleme</div><div>🤖 AI site tasarımı</div><div>↩ Geri al / yinele</div><div>📱 Canlı mobil önizleme</div><div>👥 Müşteri proje erişimi</div><div>📦 Site ZIP export motoru</div></div></section>
