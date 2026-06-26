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

<style>
.analytics-dashboard { padding: 0; }
.ao-grid.four { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
.ao-grid.two { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
@media(max-width: 1024px) { .ao-grid.four { grid-template-columns: repeat(2, 1fr); } }
@media(max-width: 768px) { .ao-grid.four, .ao-grid.two { grid-template-columns: 1fr; } }

.ao-card.metric { display: flex; align-items: center; gap: 16px; padding: 24px; }
.metric-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
.metric-content { flex: 1; }
.metric-value { font-size: 1.75rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
.metric-label { font-size: 0.9rem; color: #64748b; margin: 4px 0; }
.metric-change { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 500; }
.metric-change.positive { background: #dcfce7; color: #166534; }
.metric-change.negative { background: #fee2e2; color: #991b1b; }

.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.card-header h3 { margin: 0; font-size: 1.1rem; color: #1e293b; }

.chart-card .chart-placeholder { padding: 20px 0; }
.chart-bars { display: flex; align-items: flex-end; justify-content: space-between; height: 200px; gap: 8px; }
.bar { width: 100%; background: linear-gradient(180deg, #2563eb, #3b82f6); border-radius: 6px 6px 0 0; position: relative; min-height: 20px; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; }
.bar-label { font-size: 0.7rem; color: #64748b; padding: 4px 0; }

.revenue-breakdown { display: flex; flex-direction: column; gap: 16px; }
.breakdown-item { }
.breakdown-info { display: flex; justify-content: space-between; margin-bottom: 6px; }
.breakdown-name { font-weight: 500; color: #1e293b; }
.breakdown-percent { color: #64748b; font-size: 0.9rem; }
.breakdown-bar { height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 4px; }
.breakdown-fill { height: 100%; background: linear-gradient(90deg, #2563eb, #06b6d4); border-radius: 4px; }
.breakdown-value { font-size: 0.85rem; color: #64748b; }
.positive { color: #16a34a; }
.negative { color: #dc2626; }
</style>
