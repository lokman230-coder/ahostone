<?php
/**
 * Revenue Analytics Dashboard
 * Gelir analizi ve KPI takibi
 */
class AhostModule_revenue_analytics {
    public static function getDashboard(): array {
        return [
            "total_revenue" => self::getTotalRevenue(),
            "monthly_revenue" => self::getMonthlyRevenue(),
            "revenue_by_product" => self::getRevenueByProduct(),
            "arpu" => self::getARPU(),
            "mrr" => self::getMRR(),
            "churn_rate" => self::getChurnRate(),
            "growth_rate" => self::getGrowthRate()
        ];
    }
    
    public static function getTotalRevenue(): float {
        return db()->query("SELECT COALESCE(SUM(amount),0) FROM invoices WHERE status='paid'")->fetchColumn() ?: 0;
    }
    
    public static function getMonthlyRevenue(int $months = 12): array {
        $data = [];
        for($i = $months - 1; $i >= 0; $i--) {
            $month = date("Y-m", strtotime("-$i months"));
            $revenue = db()->prepare("SELECT COALESCE(SUM(amount),0) FROM invoices WHERE status='paid' AND DATE_FORMAT(paid_at,'%Y-%m')=?")
                ->execute([$month])->fetchColumn() ?: 0;
            $data[] = ["month"=>$month, "revenue"=>$revenue];
        }
        return $data;
    }
    
    public static function getRevenueByProduct(): array {
        return db()->query("SELECT p.name, SUM(i.amount) as total FROM invoices i JOIN invoice_items ii ON ii.invoice_id=i.id JOIN products p ON p.id=ii.product_id WHERE i.status='paid' GROUP BY p.id")->fetchAll();
    }
    
    public static function getARPU(): float {
        $revenue = self::getTotalRevenue();
        $customers = db()->query("SELECT COUNT(*) FROM customers")->fetchColumn() ?: 1;
        return round($revenue / $customers, 2);
    }
    
    public static function getMRR(): float {
        return db()->query("SELECT COALESCE(SUM(monthly_price),0) FROM hosting WHERE status='active'")->fetchColumn() ?: 0;
    }
    
    public static function getChurnRate(): float {
        $total = db()->query("SELECT COUNT(*) FROM hosting")->fetchColumn() ?: 1;
        $churned = db()->query("SELECT COUNT(*) FROM hosting WHERE status IN ('cancelled','terminated')")->fetchColumn() ?: 0;
        return round(($churned / $total) * 100, 2);
    }
    
    public static function getGrowthRate(): float {
        $thisMonth = self::getMonthlyRevenue(1)[0]["revenue"] ?? 0;
        $lastMonth = self::getMonthlyRevenue(2)[1]["revenue"] ?? 1;
        return $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2) : 0;
    }
}
