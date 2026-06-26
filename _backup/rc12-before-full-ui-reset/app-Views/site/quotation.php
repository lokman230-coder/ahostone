<?php
/**
 * Ahost One v25.0.0 RC5 - Teklif Formu
 * Site ziyaretçileri için teklif talebi formu
 */

$submitted = false;
$quotationNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    // Form verilerini al
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $serviceType = $_POST['service_type'] ?? 'website';
    $projectName = trim($_POST['project_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $budget = $_POST['budget'] ?? '';
    $timeline = $_POST['timeline'] ?? '';
    $urgency = $_POST['urgency'] ?? 'normal';
    
    // Validasyon
    $errors = [];
    if (empty($name)) $errors[] = 'Ad soyad gerekli';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli e-posta gerekli';
    if (empty($projectName)) $errors[] = 'Proje adı gerekli';
    
    if (empty($errors)) {
        // Veritabanına kaydet
        try {
            $db = get_db();
            
            // Teklif numarası oluştur
            $quotationNumber = 'TQ-' . date('Ymd') . '-' . strtoupper(substr(md5(time()), 0, 6));
            
            // Bütçe ayrıştırma
            $budgetMin = null;
            $budgetMax = null;
            if ($budget) {
                $budgetParts = explode('-', str_replace(['.', ' '], '', $budget));
                $budgetMin = isset($budgetParts[0]) ? floatval($budgetParts[0]) : null;
                $budgetMax = isset($budgetParts[1]) ? floatval($budgetParts[1]) : $budgetMin;
            }
            
            // Hizmet türü eşleme
            $serviceTypeMap = [
                'website' => 'website',
                'mobile_app' => 'mobile_app',
                'web_app' => 'web_app',
                'custom_software' => 'custom_software',
                'other' => 'other'
            ];
            
            // Features JSON
            $features = json_encode([
                'budget_range' => $budget,
                'timeline' => $timeline,
                'description' => $description
            ]);
            
            $stmt = $db->prepare("
                INSERT INTO quotations (
                    quotation_number, customer_name, customer_email, customer_phone, 
                    customer_company, service_type, project_name, project_description,
                    features, budget_min, budget_max, urgency, status, source, 
                    referer_url, ip_address, user_agent, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'website', ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $quotationNumber,
                $name,
                $email,
                $phone,
                $company,
                $serviceTypeMap[$serviceType] ?? 'website',
                $projectName,
                $description,
                $features,
                $budgetMin,
                $budgetMax,
                $urgency,
                $_SERVER['HTTP_REFERER'] ?? '',
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            $submitted = true;
            
        } catch (Exception $e) {
            $errors[] = 'Kayıt sırasında hata: ' . $e->getMessage();
        }
    }
}
?>

<section class="ao-site-content ao-quotation-page">
  <div class="ao-content-shell">
<div class="quotation-page">
        <div class="quotation-container">
            <div class="quotation-header">
                <h1>Teklif Alın</h1>
                <p>Web sitesi, mobil uygulama veya özel yazılım için bize ulaşın. Ekibimiz en kısa sürede size özel bir teklif hazırlayacaktır.</p>
            </div>
            
            <?php if ($submitted): ?>
            <div class="quotation-form-card">
                <div class="success-message">
                    <div class="success-icon">✅</div>
                    <h2>Talebiniz Alındı!</h2>
                    <p>Teklif talebiniz başarıyla iletildi. En kısa sürede sizinle iletişime geçeceğiz.</p>
                    <div class="quotation-number"><?= e($quotationNumber) ?></div>
                    <p style="font-size: 13px; color: #94a3b8;">Bu numarayı not alın. Görüşmelerde referans olarak kullanabilirsiniz.</p>
                    <a href="<?= url('') ?>" class="submit-btn" style="display: inline-block; width: auto; text-decoration: none; margin-top: 20px;">← Ana Sayfaya Dön</a>
                </div>
            </div>
            <?php else: ?>
            <div class="quotation-form-card">
                <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <strong>Lütfen hataları düzeltin:</strong>
                    <ul>
                        <?php foreach ($errors as $e): ?>
                        <li><?= e($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= url('teklif') ?>">
                    <!-- Hizmet Türü -->
                    <div class="form-section">
                        <h3>📋 Hizmet Türü</h3>
                        <div class="service-cards">
                            <label class="service-card">
                                <input type="radio" name="service_type" value="website" <?= ($_POST['service_type'] ?? '') === 'website' ? 'checked' : '' ?> required>
                                <div class="service-card-icon">🌐</div>
                                <div class="service-card-title">Web Sitesi</div>
                            </label>
                            <label class="service-card">
                                <input type="radio" name="service_type" value="mobile_app" <?= ($_POST['service_type'] ?? '') === 'mobile_app' ? 'checked' : '' ?>>
                                <div class="service-card-icon">📱</div>
                                <div class="service-card-title">Mobil Uygulama</div>
                            </label>
                            <label class="service-card">
                                <input type="radio" name="service_type" value="web_app" <?= ($_POST['service_type'] ?? '') === 'web_app' ? 'checked' : '' ?>>
                                <div class="service-card-icon">💻</div>
                                <div class="service-card-title">Web Uygulaması</div>
                            </label>
                            <label class="service-card">
                                <input type="radio" name="service_type" value="custom_software" <?= ($_POST['service_type'] ?? '') === 'custom_software' ? 'checked' : '' ?>>
                                <div class="service-card-icon">⚙️</div>
                                <div class="service-card-title">Özel Yazılım</div>
                            </label>
                            <label class="service-card">
                                <input type="radio" name="service_type" value="other" <?= ($_POST['service_type'] ?? '') === 'other' ? 'checked' : '' ?>>
                                <div class="service-card-icon">📦</div>
                                <div class="service-card-title">Diğer</div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Kişisel Bilgiler -->
                    <div class="form-section">
                        <h3>👤 Kişisel Bilgiler</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Ad Soyad *</label>
                                <input type="text" id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>" required placeholder="Adınız Soyadınız">
                            </div>
                            <div class="form-group">
                                <label for="email">E-posta *</label>
                                <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required placeholder="ornek@email.com">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Telefon</label>
                                <input type="tel" id="phone" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+90 555 000 00 00">
                            </div>
                            <div class="form-group">
                                <label for="company">Şirket Adı</label>
                                <input type="text" id="company" name="company" value="<?= e($_POST['company'] ?? '') ?>" placeholder="Şirket adı (opsiyonel)">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Proje Detayları -->
                    <div class="form-section">
                        <h3>📝 Proje Detayları</h3>
                        <div class="form-group">
                            <label for="project_name">Proje Adı *</label>
                            <input type="text" id="project_name" name="project_name" value="<?= e($_POST['project_name'] ?? '') ?>" required placeholder="Örn: E-ticaret sitesi, Mobil banka uygulaması">
                        </div>
                        <div class="form-group">
                            <label for="description">Proje Açıklaması</label>
                            <textarea id="description" name="description" placeholder="Projeniz hakkında detaylı bilgi verin. İşlevler, hedefler, özel gereksinimler..."><?= e($_POST['description'] ?? '') ?></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="budget">Tahmini Bütçe</label>
                                <select id="budget" name="budget">
                                    <option value="">Seçiniz</option>
                                    <option value="10000-25000">₺10.000 - ₺25.000</option>
                                    <option value="25000-50000">₺25.000 - ₺50.000</option>
                                    <option value="50000-100000">₺50.000 - ₺100.000</option>
                                    <option value="100000-250000">₺100.000 - ₺250.000</option>
                                    <option value="250000-500000">₺250.000 - ₺500.000</option>
                                    <option value="500000+">₺500.000+</option>
                                    <option value="belirtilmedi">Henüz belirtilmedi</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="timeline">Hedef Tamamlanma</label>
                                <select id="timeline" name="timeline">
                                    <option value="">Seçiniz</option>
                                    <option value="1-2-ay">1-2 Ay</option>
                                    <option value="2-4-ay">2-4 Ay</option>
                                    <option value="4-6-ay">4-6 Ay</option>
                                    <option value="6-12-ay">6-12 Ay</option>
                                    <option value="12+">12+ Ay</option>
                                    <option value="belirtilmedi">Henüz belirtilmedi</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Öncelik -->
                    <div class="form-section">
                        <h3>⚡ Öncelik Düzeyi</h3>
                        <div class="urgency-options">
                            <label class="urgency-option urgency-low">
                                <input type="radio" name="urgency" value="low">
                                <div>🟢</div>
                                <div>Düşük</div>
                            </label>
                            <label class="urgency-option urgency-normal selected">
                                <input type="radio" name="urgency" value="normal" checked>
                                <div>🔵</div>
                                <div>Normal</div>
                            </label>
                            <label class="urgency-option urgency-high">
                                <input type="radio" name="urgency" value="high">
                                <div>🟠</div>
                                <div>Yüksek</div>
                            </label>
                            <label class="urgency-option urgency-urgent">
                                <input type="radio" name="urgency" value="urgent">
                                <div>🔴</div>
                                <div>Acil</div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="info-note">
                        <strong>📌 Bilgi:</strong>
                        Formu doldurduktan sonra, ekibimiz 24 saat içinde sizinle iletişime geçecek ve projeniz için detaylı bir teklif hazırlayacaktır.
                    </div>
                    
                    <button type="submit" class="submit-btn">📤 Teklif Talebini Gönder</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    // Service card seçimi
    document.querySelectorAll('.service-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Urgency seçimi
    document.querySelectorAll('.urgency-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.urgency-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    </script>
  </div>
</section>
