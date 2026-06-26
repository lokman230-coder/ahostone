<?php
// E-Invoice Admin Panel
ao_e_invoice_ensure_schema();

$stats = [
    'total' => db()->query("SELECT COUNT(*) FROM invoices")->fetchColumn(),
    'draft' => db()->query("SELECT COUNT(*) FROM invoices WHERE status='draft'")->fetchColumn(),
    'sent' => db()->query("SELECT COUNT(*) FROM invoices WHERE status='sent'")->fetchColumn(),
    'paid' => db()->query("SELECT COUNT(*) FROM invoices WHERE status='paid'")->fetchColumn(),
    'total_amount' => db()->query("SELECT SUM(total) FROM invoices WHERE status='paid'")->fetchColumn() ?: 0,
];

$invoices = db()->query("SELECT i.*, c.name as customer_name 
    FROM invoices i 
    LEFT JOIN customers c ON c.id=i.customer_id 
    ORDER BY i.created_at DESC LIMIT 50")->fetchAll();

$settings = [];
try {
    $settings_q = db()->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'einvoice_%'")->fetchAll();
    foreach($settings_q as $s) { $settings[$s['setting_key']] = $s['setting_value']; }
} catch(Throwable $e) {}
?>
<style>
.invoice-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 24px; }
.invoice-stat { background: linear-gradient(135deg, #1e40af, #2563eb); color: #fff; padding: 20px; border-radius: 16px; text-align: center; }
.invoice-stat.success { background: linear-gradient(135deg, #059669, #10b981); }
.invoice-stat.warning { background: linear-gradient(135deg, #d97706, #f59e0b); }
.invoice-stat strong { display: block; font-size: 2rem; margin-bottom: 4px; }
.invoice-number { font-family: monospace; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; }
.invoice-type-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
.invoice-type-badge.invoice { background: #dbeafe; color: #1e40af; }
.invoice-type-badge.proforma { background: #fef3c7; color: #92400e; }
.invoice-type-badge.receipt { background: #dcfce7; color: #166534; }
</style>

<div class="ao-page-head">
    <div>
        <h2>📄 E-Fatura & Proforma</h2>
        <p>Türkiye uyumlu e-fatura, proforma fatura ve makbuz sistemi.</p>
    </div>
    <div class="ao-actions">
        <a class="ao-btn" href="<?= url('admin/e-invoice/create') ?>">+ Yeni Fatura</a>
        <a class="ao-btn secondary" href="<?= url('admin/e-invoice/proforma/create') ?>">Proforma Oluştur</a>
    </div>
</div>

<div class="invoice-stats">
    <div class="invoice-stat">
        <strong><?= $stats['total'] ?></strong>
        <span>Toplam Fatura</span>
    </div>
    <div class="invoice-stat warning">
        <strong><?= $stats['draft'] ?></strong>
        <span>Taslak</span>
    </div>
    <div class="invoice-stat">
        <strong><?= $stats['sent'] ?></strong>
        <span>Gönderilen</span>
    </div>
    <div class="invoice-stat success">
        <strong><?= number_format($stats['total_amount'], 0) ?> ₺</strong>
        <span>Toplam Tahsilat</span>
    </div>
</div>

<div class="ao-tabs" data-ao-tabs>
    <button class="active" data-tab="invoices">Faturalar</button>
    <button data-tab="proforma">Proforma</button>
    <button data-tab="settings">Ayarlar</button>
</div>

<div id="tab-invoices" class="ao-tab-panel active">
    <div class="ao-card">
        <table class="ao-table">
            <thead>
                <tr>
                    <th>Fatura No</th>
                    <th>Müşteri</th>
                    <th>Tür</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($invoices as $inv): ?>
                <tr>
                    <td><span class="invoice-number"><?= e($inv['invoice_number']) ?></span></td>
                    <td><?= e($inv['customer_name'] ?? $inv['customer_name']) ?></td>
                    <td><span class="invoice-type-badge <?= e($inv['type']) ?>"><?= ucfirst(e($inv['type'])) ?></span></td>
                    <td><strong><?= number_format($inv['total'], 2) ?> <?= e($inv['currency']) ?></strong></td>
                    <td>
                        <?php
                        $status_class = match($inv['status']) {
                            'draft' => 'secondary',
                            'sent' => 'warning',
                            'paid' => 'success',
                            'cancelled' => 'error',
                            default => 'secondary'
                        };
                        ?>
                        <span class="ao-badge <?= $status_class ?>"><?= ucfirst(e($inv['status'])) ?></span>
                    </td>
                    <td><small><?= date('d.m.Y', strtotime($inv['created_at'])) ?></small></td>
                    <td>
                        <a href="<?= url('admin/e-invoice/view/' . $inv['id']) ?>" class="ao-mini-btn">👁</a>
                        <a href="<?= url('admin/e-invoice/pdf/' . $inv['id']) ?>" class="ao-mini-btn">📥</a>
                        <?php if($inv['status'] === 'draft'): ?>
                        <a href="<?= url('admin/e-invoice/send/' . $inv['id']) ?>" class="ao-mini-btn">📤</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; if(empty($invoices)): ?>
                <tr><td colspan="7" style="text-align:center;padding:40px;color:#64748b">Henüz fatura yok.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="tab-proforma" class="ao-tab-panel">
    <div class="ao-card">
        <h3>Proforma Faturalar</h3>
        <p style="color:#64748b">Proforma faturalar henüz ödeme yapılmamış siparişler için kullanılır. Ödeme sonrası gerçek fatura dönüştürülebilir.</p>
        <a class="ao-btn" href="<?= url('admin/e-invoice/proforma/create') ?>" style="margin-top:16px">+ Yeni Proforma</a>
    </div>
</div>

<div id="tab-settings" class="ao-tab-panel">
    <div class="ao-card">
        <h3>Fatura Ayarları</h3>
        <form method="post" action="<?= url('admin/e-invoice/settings-save') ?>">
            <?= csrf_field() ?>
            <div class="ao-form-grid">
                <label>
                    Firma Adı
                    <input type="text" name="einvoice_company_name" value="<?= e($settings['einvoice_company_name'] ?? '') ?>">
                </label>
                <label>
                    Vergi Numarası
                    <input type="text" name="einvoice_tax_number" value="<?= e($settings['einvoice_tax_number'] ?? '') ?>">
                </label>
                <label>
                    Vergi Dairesi
                    <input type="text" name="einvoice_tax_office" value="<?= e($settings['einvoice_tax_office'] ?? '') ?>">
                </label>
                <label>
                    Telefon
                    <input type="text" name="einvoice_phone" value="<?= e($settings['einvoice_phone'] ?? '') ?>">
                </label>
            </div>
            <label class="full" style="margin-top:16px">
                Adres
                <textarea name="einvoice_address" rows="2"><?= e($settings['einvoice_address'] ?? '') ?></textarea>
            </label>
            <div class="ao-form-grid" style="margin-top:16px">
                <label>
                    Fatura Ön Ek
                    <input type="text" name="einvoice_prefix" value="<?= e($settings['einvoice_prefix'] ?? 'INV') ?>">
                </label>
                <label>
                    Varsayılan KDV (%)
                    <input type="number" name="einvoice_default_tax" value="<?= e($settings['einvoice_default_tax'] ?? '18') ?>">
                </label>
            </div>
            <button type="submit" class="ao-btn" style="margin-top:20px">💾 Kaydet</button>
        </form>
    </div>
    
    <div class="ao-card" style="margin-top:24px">
        <h3>📋 GİB Entegrasyonu (Opsiyonel)</h3>
        <p style="color:#64748b">GİB (Gelir İdaresi Başkanlığı) entegrasyonu için entegratör firma bilgilerini girin.</p>
        <div class="ao-grid two">
            <div>
                <h4 style="margin-bottom:12px">Entegratörler</h4>
                <ul style="color:#64748b">
                    <li>Logo Yazılım</li>
                    <li>Mikro Yazılım</li>
                    <li>IASOFT</li>
                    <li>Paraşüt</li>
                    <li>İdea Online</li>
                </ul>
            </div>
            <div class="ao-form">
                <label>Entegratör Seçin</label>
                <select>
                    <option value="">Seçin...</option>
                    <option>Logo Yazılım</option>
                    <option>Mikro Yazılım</option>
                    <option>Paraşüt</option>
                </select>
            </div>
        </div>
    </div>
</div>
