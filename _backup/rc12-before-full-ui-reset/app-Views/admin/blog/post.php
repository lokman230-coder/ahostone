<?php
// Blog Post Editor
$id = (int)($_GET['id'] ?? 0);
$post = null;
if($id) {
    $post = db()->prepare("SELECT * FROM blog_posts WHERE id=?")->execute([$id]) ? db()->query("SELECT * FROM blog_posts WHERE id=$id")->fetch() : null;
}
$categories = db()->query("SELECT * FROM blog_categories WHERE is_active=1 ORDER BY sort_order ASC")->fetchAll();
$tags = db()->query("SELECT * FROM blog_tags ORDER BY name ASC")->fetchAll();
?>
<div class="ao-page-head">
    <div>
        <h2><?= $id ? '📝 Yazı Düzenle' : '📝 Yeni Yazı' ?></h2>
    </div>
</div>

<div class="ao-grid two" style="gap:20px">
    <div class="ao-card">
        <h3>Yazı İçeriği</h3>
        <form method="post" action="<?= url('admin/blog/post-save') ?>">
            <?= csrf_field() ?>
            <?php if($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
            
            <div class="ao-form">
                <div class="ao-form-grid">
                    <label class="full">Başlık <input type="text" name="title" id="postTitle" value="<?= e($post['title'] ?? '') ?>" required></label>
                    
                    <label class="full">Slug <input type="text" name="slug" id="postSlug" value="<?= e($post['slug'] ?? '') ?>" placeholder="otomatik-olarak-olusturulur">
                    <small style="color:#64748b">Boş bırakırsanız başlıktan otomatik oluşturulur</small></label>
                    
                    <label class="full">Özet <textarea name="excerpt" rows="3" placeholder="SEO için kısa açıklama (150-160 karakter)"><?= e($post['excerpt'] ?? '') ?></textarea></label>
                    
                    <label class="full">İçerik <textarea name="content" id="postContent" rows="15" placeholder="Yazı içeriği..."><?= e($post['content'] ?? '') ?></textarea></label>
                    
                    <label>Kategori <select name="category_id">
                        <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($post['category_id'] ?? 1) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select></label>
                    
                    <label>Etiketler <input type="text" name="tags" value="" placeholder="etiket1, etiket2, etiket3"></label>
                    
                    <label>Görsel URL <input type="url" name="featured_image" value="<?= e($post['featured_image'] ?? '') ?>" placeholder="https://..."></label>
                    
                    <label>Yayın Durumu <select name="status">
                        <option value="draft" <?= ($post['status'] ?? 'draft') == 'draft' ? 'selected' : '' ?>>Taslak</option>
                        <option value="published" <?= ($post['status'] ?? '') == 'published' ? 'selected' : '' ?>>Yayınla</option>
                        <option value="scheduled" <?= ($post['status'] ?? '') == 'scheduled' ? 'selected' : '' ?>>Zamanlı</option>
                    </select></label>
                    
                    <label>Yayın Tarihi <input type="datetime-local" name="published_at" value="<?= $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '' ?>"></label>
                </div>
            </div>
            
            <hr style="margin:20px 0">
            <h4>SEO Ayarları</h4>
            <div class="ao-form-grid">
                <label class="full">Meta Başlık <input type="text" name="meta_title" value="<?= e($post['meta_title'] ?? '') ?>" maxlength="60">
                <small style="color:#64748b">60 karakter önerilir</small></label>
                
                <label class="full">Meta Açıklama <textarea name="meta_description" rows="2" maxlength="160"><?= e($post['meta_description'] ?? '') ?></textarea>
                <small style="color:#64748b">160 karakter önerilir</small></label>
                
                <label class="full">Anahtar Kelimeler <input type="text" name="meta_keywords" value="<?= e($post['meta_keywords'] ?? '') ?>" placeholder="hosting, domain, ssl"></label>
            </div>
            
            <div class="ao-form-grid" style="margin-top:16px">
                <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="featured" value="1" <?= !empty($post['featured']) ? 'checked' : '' ?>> Öne çıkan yazı</label>
                <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="sticky" value="1" <?= !empty($post['sticky']) ? 'checked' : '' ?>> Sabit (üstte kal)</label>
                <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="allow_comments" value="1" <?= ($post['allow_comments'] ?? 1) ? 'checked' : '' ?>> Yorumlara izin ver</label>
            </div>
            
            <div style="margin-top:20px;display:flex;gap:12px">
                <button type="submit" class="ao-btn">💾 Kaydet</button>
                <a class="ao-btn secondary" href="<?= url('admin/blog') ?>">İptal</a>
                <?php if($id): ?>
                <a class="ao-btn soft" target="_blank" href="<?= url('blog/'.$post['slug']) ?>">👁 Önizle</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div>
        <div class="ao-card" style="margin-bottom:16px">
            <h4>📸 Görsel Yükle</h4>
            <form id="imageUpload" enctype="multipart/form-data">
                <input type="file" name="image" accept="image/*" style="width:100%;padding:10px;border:1px dashed #dbe5f2;border-radius:12px">
                <button type="button" class="ao-btn" style="margin-top:8px" onclick="uploadImage()">Yükle</button>
            </form>
            <p id="imageResult" style="margin-top:8px;font-size:12px"></p>
        </div>
        
        <div class="ao-card">
            <h4>📊 SEO Durumu</h4>
            <div id="seoPreview" style="background:#f8fafc;padding:16px;border-radius:12px">
                <p style="margin:0 0 8px;font-size:13px;color:#64748b">Google Önizleme</p>
                <div id="seoTitle" style="color:#1a0dab;font-size:18px;margin-bottom:4px"><?= e($post['meta_title'] ?: ($post['title'] ?: 'Başlık girin...')) ?></div>
                <div id="seoUrl" style="color:#006621;font-size:14px"><?= e($_SERVER['HTTP_HOST'] ?? 'site.com') ?>/blog/<span id="seoSlug"><?= e($post['slug'] ?: 'yazi-basligi') ?></span></div>
                <div id="seoDesc" style="color:#545722;font-size:14px"><?= e($post['meta_description'] ?: ($post['excerpt'] ?: 'Açıklama girin...')) ?></div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-generate slug from title
document.getElementById('postTitle')?.addEventListener('input', function() {
    const title = this.value;
    const slug = title.toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('postSlug').value = slug || '';
    document.getElementById('seoSlug').textContent = slug || 'yazi-basligi';
    document.getElementById('seoTitle').textContent = title || 'Başlık girin...';
});

// Update SEO preview
document.getElementById('postSlug')?.addEventListener('input', function() {
    document.getElementById('seoSlug').textContent = this.value || 'yazi-basligi';
});
document.querySelector('[name="meta_title"]')?.addEventListener('input', function() {
    document.getElementById('seoTitle').textContent = this.value || document.getElementById('postTitle').value || 'Başlık girin...';
});
document.querySelector('[name="meta_description"]')?.addEventListener('input', function() {
    document.getElementById('seoDesc').textContent = this.value || 'Açıklama girin...';
});

// Simple image upload (placeholder - implement with your upload handler)
function uploadImage() {
    const file = document.querySelector('#imageUpload input[type=file]').files[0];
    if(!file) { document.getElementById('imageResult').innerHTML = '⚠️ Dosya seçin'; return; }
    // For demo: just show the filename
    document.getElementById('imageResult').innerHTML = '✅ ' + file.name + ' seçildi (Gerçek yükleme için upload handler eklenmeli)';
    document.querySelector('[name="featured_image"]').value = '/uploads/blog/' + file.name;
}
</script>
