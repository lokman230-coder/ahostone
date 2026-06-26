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

<style>
.points-admin {}
.points-hero { background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); color: #fff; border-radius: 20px; padding: 40px; text-align: center; margin-bottom: 24px; }
.hero-icon { font-size: 4rem; margin-bottom: 16px; }
.points-hero h1 { font-size: 2rem; margin: 0 0 8px; }
.points-hero p { opacity: 0.9; margin: 0; }

.ao-grid.four { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
@media(max-width: 1024px) { .ao-grid.four { grid-template-columns: repeat(2, 1fr); } }
@media(max-width: 640px) { .ao-grid.four { grid-template-columns: 1fr; } }

.stat-card { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 24px; }
.stat-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 12px; }
.stat-value { font-size: 1.75rem; font-weight: 700; color: #1e293b; }
.stat-label { font-size: 0.9rem; color: #64748b; }

.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.card-header h3 { margin: 0; }

.levels-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
@media(max-width: 768px) { .levels-grid { grid-template-columns: repeat(2, 1fr); } }
.level-card { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 16px; padding: 24px; text-align: center; }
.level-card.current { border-color: #2563eb; background: #eff6ff; }
.level-badge { font-size: 2.5rem; margin-bottom: 12px; }
.level-name { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
.level-points { font-size: 0.9rem; color: #64748b; margin-bottom: 8px; }
.level-multiplier { font-size: 0.85rem; color: #2563eb; font-weight: 500; }

.rewards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
@media(max-width: 768px) { .rewards-grid { grid-template-columns: 1fr; } }
.reward-card { display: flex; align-items: center; gap: 16px; padding: 20px; background: #f8fafc; border-radius: 12px; }
.reward-icon { width: 48px; height: 48px; background: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.reward-info { flex: 1; }
.reward-info strong { display: block; color: #1e293b; }
.reward-info span { font-size: 0.9rem; color: #64748b; }
.reward-stats .stat { font-size: 0.85rem; color: #10b981; }
</style>
