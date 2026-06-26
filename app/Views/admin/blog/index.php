<?php
// Blog Admin Panel
ao_ensure_blog_schema();
$posts = db()->query("SELECT p.*, c.name as category_name, u.name as author_name 
    FROM blog_posts p 
    LEFT JOIN blog_categories c ON c.id=p.category_id 
    LEFT JOIN admins u ON u.id=p.author_id 
    ORDER BY p.created_at DESC LIMIT 100")->fetchAll();
$categories = db()->query("SELECT * FROM blog_categories ORDER BY sort_order ASC")->fetchAll();
$stats = [
    'total' => db()->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn(),
    'published' => db()->query("SELECT COUNT(*) FROM blog_posts WHERE status='published'")->fetchColumn(),
    'draft' => db()->query("SELECT COUNT(*) FROM blog_posts WHERE status='draft'")->fetchColumn(),
    'comments' => db()->query("SELECT COUNT(*) FROM blog_comments WHERE status='pending'")->fetchColumn()
];
?>
<div class="ao-page-head">
    <div>
        <h2>📝 Blog Yönetimi</h2>
        <p>SEO uyumlu blog yazıları, kategoriler ve yorumlar.</p>
    </div>
    <div class="ao-actions">
        <a class="ao-btn" href="<?= url('admin/blog/post') ?>">+ Yeni Yazı</a>
        <a class="ao-btn secondary" href="<?= url('admin/blog/categories') ?>">📁 Kategoriler</a>
    </div>
</div>

<div class="ao-stats-grid">
    <div class="ao-stat"><span>Toplam Yazı</span><strong><?= $stats['total'] ?></strong></div>
    <div class="ao-stat"><span>Yayınlanan</span><strong><?= $stats['published'] ?></strong></div>
    <div class="ao-stat"><span>Taslak</span><strong><?= $stats['draft'] ?></strong></div>
    <div class="ao-stat"><span>Onay Bekleyen</span><strong><?= $stats['comments'] ?></strong></div>
</div>

<div class="ao-tabs" data-ao-tabs>
    <button class="active" data-tab="posts">Yazılar</button>
    <button data-tab="comments">Yorumlar</button>
    <button data-tab="settings">Ayarlar</button>
</div>

<div id="tab-posts" class="ao-tab-panel active">
    <div class="ao-card">
        <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
            <input type="search" id="blogSearch" placeholder="Yazı ara..." style="flex:1;min-width:200px;padding:10px;border:1px solid var(--ao-border);border-radius:12px">
            <select id="blogFilter" style="padding:10px;border:1px solid var(--ao-border);border-radius:12px">
                <option value="">Tümü</option>
                <option value="published">Yayınlanan</option>
                <option value="draft">Taslak</option>
                <option value="scheduled">Zamanlı</option>
            </select>
        </div>
        <table class="ao-table">
            <thead>
                <tr>
                    <th>Görsel</th>
                    <th>Başlık</th>
                    <th>Kategori</th>
                    <th>Durum</th>
                    <th>Görüntülenme</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($posts as $p): ?>
                <tr>
                    <td>
                        <?php if($p['featured_image']): ?>
                        <img src="<?= e($p['featured_image']) ?>" style="width:60px;height:40px;object-fit:cover;border-radius:8px">
                        <?php else: ?>
                        <div style="width:60px;height:40px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px">📝</div>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?= url('admin/blog/post?id='.$p['id']) ?>"><strong><?= e($p['title']) ?></strong></a><br><small><?= e($p['slug']) ?></small></td>
                    <td><?= e($p['category_name'] ?? 'Genel') ?></td>
                    <td><span class="ao-badge <?= $p['status'] ?>"><?= e($p['status']) ?></span></td>
                    <td><?= number_format($p['view_count']) ?></td>
                    <td><small><?= date('d.m.Y', strtotime($p['created_at'])) ?></small></td>
                    <td>
                        <a class="ao-mini-btn" href="<?= url('admin/blog/post?id='.$p['id']) ?>">Düzenle</a>
                        <a class="ao-mini-btn" target="_blank" href="<?= url('blog/'.$p['slug']) ?>">Gör</a>
                        <a class="ao-mini-btn danger" href="<?= url('admin/blog/delete?id='.$p['id'].'&csrf_token='.csrf_token()) ?>" onclick="return confirm('Silinecek?')">Sil</a>
                    </td>
                </tr>
            <?php endforeach; if(empty($posts)): ?>
                <tr><td colspan="7" style="text-align:center;padding:40px">Henüz yazı yok. <a href="<?= url('admin/blog/post') ?>">İlk yazıyı yazın</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="tab-comments" class="ao-tab-panel">
    <?php $pending = db()->query("SELECT c.*, p.title as post_title FROM blog_comments c LEFT JOIN blog_posts p ON p.id=c.post_id WHERE c.status='pending' ORDER BY c.created_at DESC")->fetchAll(); ?>
    <div class="ao-card">
        <h3>Onay Bekleyen Yorumlar (<?= count($pending) ?>)</h3>
        <?php if($pending): ?>
        <table class="ao-table">
            <thead><tr><th>Yazı</th><th>Yazar</th><th>Yorum</th><th>Tarih</th><th>İşlem</th></tr></thead>
            <tbody>
            <?php foreach($pending as $c): ?>
                <tr>
                    <td><small><?= e($c['post_title']) ?></small></td>
                    <td><?= e($c['author_name']) ?><br><small><?= e($c['author_email']) ?></small></td>
                    <td><?= e(substr($c['content'], 0, 100)) ?>...</td>
                    <td><small><?= date('d.m.Y H:i', strtotime($c['created_at'])) ?></small></td>
                    <td>
                        <a class="ao-mini-btn" href="<?= url('admin/blog/comment-approve?id='.$c['id'].'&csrf_token='.csrf_token()) ?>">Onayla</a>
                        <a class="ao-mini-btn danger" href="<?= url('admin/blog/comment-spam?id='.$c['id'].'&csrf_token='.csrf_token()) ?>">Spam</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align:center;padding:40px;color:#64748b">Onay bekleyen yorum yok.</p>
        <?php endif; ?>
    </div>
</div>

<div id="tab-settings" class="ao-tab-panel">
    <div class="ao-card">
        <h3>Blog Ayarları</h3>
        <form method="post" action="<?= url('admin/blog/settings-save') ?>">
            <?= csrf_field() ?>
            <div class="ao-form-grid">
                <label>Blog Adı<input type="text" name="blog_name" value="Ahost One Blog"></label>
                <label>Slogan<input type="text" name="blog_tagline" value="Hosting ve teknoloji rehberleri"></label>
                <label>Sayfa Başına Yazı<input type="number" name="posts_per_page" value="10"></label>
            </div>
            <div style="margin-top:16px">
                <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="comments_moderation" checked> Yorum moderasyonu aktif</label>
                <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="featured_posts" checked> Öne çıkan yazılar</label>
                <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="share_buttons" checked> Sosyal paylaşım butonları</label>
            </div>
            <button type="submit" class="ao-btn" style="margin-top:16px">Kaydet</button>
        </form>
    </div>
</div>

<script>
document.getElementById('blogSearch')?.addEventListener('input', e => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#tab-posts tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
document.getElementById('blogFilter')?.addEventListener('change', e => {
    const val = e.target.value;
    document.querySelectorAll('#tab-posts tbody tr').forEach(row => {
        if(!val) { row.style.display = ''; return; }
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>
