<?php
class MigrationStager
{
    private PDO $target;

    public function __construct(PDO $target)
    {
        $this->target = $target;
        $this->target->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createScan(?int $connectionId, string $sourceType, array $data): int
    {
        $summary = [];
        foreach ($data as $type => $rows) $summary[$type] = is_array($rows) ? count($rows) : 0;
        $stmt = $this->target->prepare("INSERT INTO module_migration_bridge_scans (connection_id, source_type, summary_json, status) VALUES (?,?,?, 'scanned')");
        $stmt->execute([$connectionId, $sourceType, json_encode($summary, JSON_UNESCAPED_UNICODE)]);
        $scanId = (int)$this->target->lastInsertId();
        foreach ($data as $type => $rows) {
            if (!is_array($rows)) continue;
            foreach ($rows as $row) {
                $sourceId = (string)($row['id'] ?? md5(json_encode($row)));
                $title = $this->titleFor($type, $row);
                $subtitle = $this->subtitleFor($type, $row);
                $conflict = $this->detectConflict($type, $row);
                $ins = $this->target->prepare("INSERT INTO module_migration_bridge_items (scan_id,item_type,source_id,title,subtitle,payload_json,action,conflict_status) VALUES (?,?,?,?,?,?,?,?)");
                $ins->execute([$scanId,$type,$sourceId,$title,$subtitle,json_encode($row, JSON_UNESCAPED_UNICODE),'import',$conflict]);
            }
        }
        $this->log($scanId, 'info', 'Dry-run taraması tamamlandı', $summary);
        return $scanId;
    }

    public function getPreview(int $scanId): array
    {
        $stmt = $this->target->prepare("SELECT * FROM module_migration_bridge_items WHERE scan_id=? ORDER BY item_type,id");
        $stmt->execute([$scanId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($items as $item) {
            $item['payload'] = json_decode($item['payload_json'] ?? '[]', true) ?: [];
            $out[$item['item_type']][] = $item;
        }
        return $out;
    }

    public function updateActions(int $scanId, array $actions): void
    {
        $stmt = $this->target->prepare("UPDATE module_migration_bridge_items SET action=? WHERE scan_id=? AND id=?");
        foreach ($actions as $id => $action) {
            if (!in_array($action, ['import','skip','update','merge','exclude'], true)) $action='skip';
            $stmt->execute([$action, $scanId, (int)$id]);
        }
    }

    public function log(?int $scanId, string $level, string $message, array $context=[]): void
    {
        $stmt = $this->target->prepare("INSERT INTO module_migration_bridge_logs (scan_id,level,message,context_json) VALUES (?,?,?,?)");
        $stmt->execute([$scanId,$level,$message,json_encode($context, JSON_UNESCAPED_UNICODE)]);
    }

    private function titleFor(string $type, array $r): string
    {
        return match($type) {
            'customers' => trim(($r['firstname'] ?? '').' '.($r['lastname'] ?? '')) ?: ($r['companyname'] ?? 'Müşteri'),
            'products' => $r['name'] ?? 'Ürün',
            'domain_extensions' => $r['extension'] ?? 'Uzantı',
            'hosting' => ($r['domain'] ?? '') ?: ('Hosting #'.($r['id'] ?? '')),
            'domains' => $r['domain'] ?? 'Domain',
            'invoices' => 'Fatura #'.($r['id'] ?? ''),
            'tickets' => $r['title'] ?? ('Ticket #'.($r['id'] ?? '')),
            'currencies' => $r['code'] ?? 'Para Birimi',
            default => $type.' #'.($r['id'] ?? '')
        };
    }

    private function subtitleFor(string $type, array $r): string
    {
        return match($type) {
            'customers' => trim(($r['email'] ?? '').' '.($r['status'] ?? '')),
            'products' => trim(($r['type'] ?? '').' '.($r['servertype'] ?? '')),
            'domain_extensions' => 'Registrar: '.($r['autoreg'] ?? '-'),
            'hosting' => trim(($r['domainstatus'] ?? '').' '.($r['billingcycle'] ?? '').' '.($r['amount'] ?? '')),
            'domains' => trim(($r['status'] ?? '').' '.($r['registrar'] ?? '').' '.($r['recurringamount'] ?? '')),
            'invoices' => trim(($r['status'] ?? '').' '.($r['total'] ?? '')),
            'tickets' => trim(($r['status'] ?? '').' '.($r['urgency'] ?? '')),
            'currencies' => 'Kur: '.($r['rate'] ?? '1'),
            default => ''
        };
    }

    private function detectConflict(string $type, array $r): string
    {
        // Çekirdek tablo isimleri kurulumdan kuruluma değişebileceği için güvenli basit kontrol.
        try {
            if ($type === 'customers' && !empty($r['email']) && $this->tableExists('customers')) {
                $q=$this->target->prepare("SELECT id FROM customers WHERE email=? LIMIT 1"); $q->execute([$r['email']]); return $q->fetchColumn() ? 'exists' : 'new';
            }
            if ($type === 'domains' && !empty($r['domain']) && $this->tableExists('domains')) {
                $q=$this->target->prepare("SELECT id FROM domains WHERE domain=? LIMIT 1"); $q->execute([$r['domain']]); return $q->fetchColumn() ? 'exists' : 'new';
            }
        } catch (Throwable $e) { return 'unknown'; }
        return 'new';
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->target->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);
        return (bool)$stmt->fetchColumn();
    }
}
