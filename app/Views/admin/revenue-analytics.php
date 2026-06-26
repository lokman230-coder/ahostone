<?php
/**
 * Revenue Analytics Dashboard
 * Ahost One - Tam uyumlu görünüm
 */
$stats = \AhostModule_revenue_analytics::getDashboard();
?>
<div class="analytics-dashboard">
    <!-- KPI Cards -->
    <div class="ao-grid four">
        <div class="ao-card metric">
            <div class="metric-icon" style="background:#eff6ff;color:#2563eb">💰</div>
            <div class="metric-content">
                <div class="metric-value">₺<?= number_format($stats['total_revenue'] ?? 0, 2) ?></div>
                <div class="metric-label">Toplam Gelir</div>
                <div class="metric-change positive">↑ %12</div>
            </div>
        </div>
        
        <div class="ao-card metric">
            <div class="metric-icon" style="background:#f0fdf4;color:#16a34a">📊</div>
            <div class="metric-content">
                <div class="metric-value">₺<?= number_format($stats['mrr'] ?? 0, 2) ?></div>
                <div class="metric-label">Aylık Tekrarlayan Gelir</div>
                <div class="metric-change positive">↑ %8</div>
            </div>
        </div>
        
        <div class="ao-card metric">
            <div class="metric-icon" style="background:#fef3c7;color:#d97706">👥</div>
            <div class="metric-content">
                <div class="metric-value">₺<?= number_format($stats['arpu'] ?? 0, 2) ?></div>
                <div class="metric-label">Ortalama Gelir/Kullanıcı</div>
                <div class="metric-change negative">↓ %3</div>
            </div>
        </div>
        
        <div class="ao-card metric">
            <div class="metric-icon" style="background:#fef2f2;color:#dc2626">📉</div>
            <div class="metric-content">
                <div class="metric-value">%<?= $stats['churn_rate'] ?? 0 ?></div>
                <div class="metric-label">Churn Rate</div>
                <div class="metric-change positive">↓ %2</div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="ao-grid two">
        <div class="ao-card chart-card">
            <div class="card-header">
                <h3>📈 Aylık Gelir</h3>
                <div class="card-actions">
                    <select class="ao-select">
                        <option>Son 12 Ay</option>
                        <option>Son 6 Ay</option>
                        <option>Bu Yıl</option>
                    </select>
                </div>
            </div>
            <div class="chart-placeholder">
                <div class="chart-bars">
                    <?php foreach($stats['monthly_revenue'] ?? [] as $i => $m): ?>
                    <div class="bar" style="height:<?= min(($m['revenue'] / 10000) * 100, 100) ?>%">
                        <span class="bar-label"><?= date('M', strtotime($m['month'].'-01')) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="ao-card">
            <div class="card-header">
                <h3>📦 Gelir Dağılımı</h3>
            </div>
            <div class="revenue-breakdown">
                <?php foreach($stats['revenue_by_product'] ?? [] as $p): ?>
                <div class="breakdown-item">
                    <div class="breakdown-info">
                        <span class="breakdown-name"><?= e($p['name']) ?></span>
                        <span class="breakdown-percent">%<?= round($p['total'] / ($stats['total_revenue'] ?: 1) * 100) ?></span>
                    </div>
                    <div class="breakdown-bar">
                        <div class="breakdown-fill" style="width:<?= ($p['total'] / ($stats['total_revenue'] ?: 1)) * 100 ?>%"></div>
                    </div>
                    <div class="breakdown-value">₺<?= number_format($p['total'], 2) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="ao-card">
        <div class="card-header">
            <h3>📋 Detaylı İstatistikler</h3>
        </div>
        <table class="ao-table">
            <thead>
                <tr>
                    <th>Metrik</th>
                    <th>Değer</th>
                    <th>Değişim</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Toplam Müşteri</td>
                    <td><strong>1,234</strong></td>
                    <td class="positive">↑ 45</td>
                    <td><span class="ao-badge success">İyi</span></td>
                </tr>
                <tr>
                    <td>Aktif Hosting</td>
                    <td><strong>892</strong></td>
                    <td class="positive">↑ 23</td>
                    <td><span class="ao-badge success">İyi</span></td>
                </tr>
                <tr>
                    <td>Toplam Domain</td>
                    <td><strong>2,156</strong></td>
                    <td class="positive">↑ 67</td>
                    <td><span class="ao-badge success">İyi</span></td>
                </tr>
                <tr>
                    <td>Bekleyen Faturalar</td>
                    <td><strong>₺45,678</strong></td>
                    <td class="negative">↑ 12</td>
                    <td><span class="ao-badge warning">Dikkat</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
