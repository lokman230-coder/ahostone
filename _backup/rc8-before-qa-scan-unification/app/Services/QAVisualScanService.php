<?php
class QAVisualScanService
{
    public static function routes(): array
    {
        return [
            ['area'=>'Site','path'=>'','label'=>'Ana Sayfa'], ['area'=>'Site','path'=>'products','label'=>'Ürünler'], ['area'=>'Site','path'=>'domain','label'=>'Domain'], ['area'=>'Site','path'=>'cart','label'=>'Sepet'], ['area'=>'Site','path'=>'blog','label'=>'Blog'], ['area'=>'Site','path'=>'knowledgebase','label'=>'Bilgi Bankası'], ['area'=>'Site','path'=>'references','label'=>'Referanslar'], ['area'=>'Site','path'=>'quotation','label'=>'Teklif'],
            ['area'=>'Admin','path'=>'admin/dashboard','label'=>'Admin Dashboard'], ['area'=>'Admin','path'=>'admin/settings','label'=>'Ayarlar Merkezi'], ['area'=>'Admin','path'=>'admin/api-integrations','label'=>'API Entegrasyonları'], ['area'=>'Admin','path'=>'admin/domain-center','label'=>'Domain Center'], ['area'=>'Admin','path'=>'admin/hosting-server','label'=>'Sunucular'], ['area'=>'Admin','path'=>'admin/automation','label'=>'Otomasyonlar'], ['area'=>'Admin','path'=>'admin/build-center','label'=>'Build Center'], ['area'=>'Admin','path'=>'admin/help-center','label'=>'Yardım Merkezi'],
            ['area'=>'Müşteri','path'=>'client/login','label'=>'Müşteri Girişi'], ['area'=>'Müşteri','path'=>'client/dashboard','label'=>'Müşteri Dashboard'], ['area'=>'Müşteri','path'=>'client/services','label'=>'Hizmetler'], ['area'=>'Müşteri','path'=>'client/domains','label'=>'Domainler'], ['area'=>'Müşteri','path'=>'client/tickets','label'=>'Destek'],
        ];
    }
    public static function createReport(string $baseUrl): string
    {
        $stamp = date('Ymd-His');
        $dir = __DIR__.'/../../storage/reports/qa-scans/'.$stamp;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $rows = [];
        foreach (self::routes() as $r) {
            $url = rtrim($baseUrl,'/').'/'.ltrim($r['path'],'/');
            $rows[] = ['area'=>$r['area'], 'label'=>$r['label'], 'url'=>$url, 'desktop'=>'Bekliyor', 'mobile'=>'Bekliyor', 'notes'=>self::notesFor($r['path'])];
        }
        $html = self::html($rows, $baseUrl, $stamp);
        file_put_contents($dir.'/report.html', $html);
        file_put_contents($dir.'/routes.json', json_encode($rows, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        file_put_contents($dir.'/README.md', "# QA Görsel Tarama Raporu\n\nGerçek ekran görüntüsü için `node tools/qa-visual-scan.js --base={$baseUrl}` komutunu çalıştırın.\n");
        return $dir;
    }
    private static function notesFor(string $path): string
    {
        if (str_contains($path,'settings')) return 'Sekme görünürlüğü ve sadece aktif panel kontrol edilecek.';
        if (str_contains($path,'api-integrations')) return 'Tüm API sağlayıcıları tek merkez ve sekmeli yapı kontrol edilecek.';
        if (str_contains($path,'build-center')) return 'Alt sayfaların aynı içeriği göstermediği kontrol edilecek.';
        if (str_contains($path,'help-center')) return 'Kılavuzdaki menü yolları gerçek panelle karşılaştırılacak.';
        return 'Responsive, 404, JS hata, taşma ve premium UI kontrolü.';
    }
    private static function html(array $rows, string $baseUrl, string $stamp): string
    {
        $tr=''; foreach($rows as $r){ $tr.='<tr><td>'.htmlspecialchars($r['area']).'</td><td>'.htmlspecialchars($r['label']).'</td><td><code>'.htmlspecialchars($r['url']).'</code></td><td>'.$r['desktop'].'</td><td>'.$r['mobile'].'</td><td>'.htmlspecialchars($r['notes']).'</td></tr>'; }
        return '<!doctype html><html lang="tr"><head><meta charset="utf-8"><title>Ahost One QA Raporu '.$stamp.'</title><style>body{font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a;margin:30px}h1{margin:0}.card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;padding:20px;box-shadow:0 20px 60px rgba(15,23,42,.08)}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}th{font-size:12px;text-transform:uppercase;color:#64748b}code{white-space:normal}.pill{display:inline-block;border-radius:999px;background:#eef2ff;color:#1d4ed8;padding:6px 10px;font-weight:700}</style></head><body><div class="card"><h1>Ahost One QA Görsel Tarama Raporu</h1><p>Base URL: <b>'.htmlspecialchars($baseUrl).'</b> — Tarih: '.$stamp.'</p><p><span class="pill">Masaüstü + Mobil</span> <span class="pill">HTML/PDF uyumlu</span> <span class="pill">Admin/Müşteri/Site</span></p><table><thead><tr><th>Alan</th><th>Sayfa</th><th>URL</th><th>Masaüstü</th><th>Mobil</th><th>Not</th></tr></thead><tbody>'.$tr.'</tbody></table></div></body></html>';
    }
}
