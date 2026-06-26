<?php
/**
 * Points System - Sadakat Programı
 * Ahost One - Site temasına uyumlu
 */
?>
<div class="points-admin">
    <!-- Balance Overview -->
    <div class="points-hero">
        <div class="hero-content">
            <div class="hero-icon">⭐</div>
            <h1>Puan Sistemi</h1>
            <p>Müşterilerinizi ödüllendirin, sadakatlerini artırın</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="ao-grid four">
        <div class="ao-card stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706">🪙</div>
            <div class="stat-value">125,450</div>
            <div class="stat-label">Dağıtılan Puan</div>
        </div>
        <div class="ao-card stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#16a34a">👥</div>
            <div class="stat-value">1,234</div>
            <div class="stat-label">Aktif Üye</div>
        </div>
        <div class="ao-card stat-card">
            <div class="stat-icon" style="background:#eff6ff;color:#2563eb">🎁</div>
            <div class="stat-value">89</div>
            <div class="stat-label">Redeem Edilen</div>
        </div>
        <div class="ao-card stat-card">
            <div class="stat-icon" style="background:#fef2f2;color:#dc2626">📈</div>
            <div class="stat-value">%67</div>
            <div class="stat-label">Dönüşüm Oranı</div>
        </div>
    </div>

    <!-- Levels -->
    <div class="ao-card">
        <div class="card-header">
            <h3>🏆 Seviye Sistemi</h3>
            <button class="ao-btn ao-btn-primary">+ Seviye Ekle</button>
        </div>
        
        <div class="levels-grid">
            <div class="level-card">
                <div class="level-badge bronze">🥉</div>
                <div class="level-name">Bronze</div>
                <div class="level-points">0 - 500 puan</div>
                <div class="level-multiplier">x1 puan kazanım</div>
            </div>
            <div class="level-card">
                <div class="level-badge silver">🥈</div>
                <div class="level-name">Silver</div>
                <div class="level-points">501 - 2000 puan</div>
                <div class="level-multiplier">x1.5 puan kazanım</div>
            </div>
            <div class="level-card current">
                <div class="level-badge gold">🥇</div>
                <div class="level-name">Gold</div>
                <div class="level-points">2001 - 5000 puan</div>
                <div class="level-multiplier">x2 puan kazanım</div>
            </div>
            <div class="level-card">
                <div class="level-badge platinum">💎</div>
                <div class="level-name">Platinum</div>
                <div class="level-points">5000+ puan</div>
                <div class="level-multiplier">x3 puan kazanım</div>
            </div>
        </div>
    </div>

    <!-- Earn Rules -->
    <div class="ao-card" style="margin-top:24px">
        <div class="card-header">
            <h3>💰 Puan Kazanma Kuralları</h3>
            <button class="ao-btn ao-btn-secondary">+ Kural Ekle</button>
        </div>
        
        <table class="ao-table">
            <thead>
                <tr>
                    <th> Aksiyon</th>
                    <th>Puan</th>
                    <th>Çarpan</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>🛒 Sipariş Tamamla</td>
                    <td><strong>10 ₺ = 1 puan</strong></td>
                    <td>1x</td>
                    <td><span class="ao-badge success">Aktif</span></td>
                    <td><button class="ao-btn-icon">✏️</button></td>
                </tr>
                <tr>
                    <td>📝 Yorum Yap</td>
                    <td><strong>50 puan</strong></td>
                    <td>1x</td>
                    <td><span class="ao-badge success">Aktif</span></td>
                    <td><button class="ao-btn-icon">✏️</button></td>
                </tr>
                <tr>
                    <td>⭐ Ürün Değerlendir</td>
                    <td><strong>100 puan</strong></td>
                    <td>1x</td>
                    <td><span class="ao-badge success">Aktif</span></td>
                    <td><button class="ao-btn-icon">✏️</button></td>
                </tr>
                <tr>
                    <td>👥 Arkadaş Davet</td>
                    <td><strong>500 puan</strong></td>
                    <td>1x</td>
                    <td><span class="ao-badge success">Aktif</span></td>
                    <td><button class="ao-btn-icon">✏️</button></td>
                </tr>
                <tr>
                    <td>📱 Mobil Uygulama İndir</td>
                    <td><strong>200 puan</strong></td>
                    <td>1x</td>
                    <td><span class="ao-badge warning">Pasif</span></td>
                    <td><button class="ao-btn-icon">✏️</button></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Rewards -->
    <div class="ao-card" style="margin-top:24px">
        <div class="card-header">
            <h3>🎁 Ödül Havuzu</h3>
            <button class="ao-btn ao-btn-primary">+ Ödül Ekle</button>
        </div>
        
        <div class="rewards-grid">
            <div class="reward-card">
                <div class="reward-icon">🎫</div>
                <div class="reward-info">
                    <strong>%10 İndirim</strong>
                    <span>500 puan</span>
                </div>
                <div class="reward-stats">
                    <span class="stat">234 kez redeem</span>
                </div>
            </div>
            <div class="reward-card">
                <div class="reward-icon">🚚</div>
                <div class="reward-info">
                    <strong>Ücretsiz Kargo</strong>
                    <span>300 puan</span>
                </div>
                <div class="reward-stats">
                    <span class="stat">456 kez redeem</span>
                </div>
            </div>
            <div class="reward-card">
                <div class="reward-icon">🎂</div>
                <div class="reward-info">
                    <strong>Doğum Günü Hediyesi</strong>
                    <span>1000 puan</span>
                </div>
                <div class="reward-stats">
                    <span class="stat">78 kez redeem</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
