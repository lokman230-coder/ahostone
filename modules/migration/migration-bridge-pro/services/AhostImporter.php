<?php
class AhostImporter
{
    private PDO $db;
    private MigrationStager $stager;
    private array $settings = [];

    public function __construct(PDO $db, MigrationStager $stager)
    {
        $this->db = $db;
        $this->stager = $stager;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->settings = $this->loadSettings();
        $this->ensureTargetPricingColumns();
    }

    public function importScan(int $scanId): array
    {
        $preview = $this->stager->getPreview($scanId);
        $report = [];
        foreach ($preview as $type => $items) {
            $report[$type] = ['imported'=>0,'skipped'=>0,'updated'=>0,'errors'=>0];
            foreach ($items as $item) {
                if (!in_array($item['action'], ['import','update','merge'], true)) { $report[$type]['skipped']++; continue; }
                try {
                    $result = $this->importItem($type, $item);
                    $report[$type][$result === 'updated' ? 'updated' : 'imported']++;
                } catch (Throwable $e) {
                    $report[$type]['errors']++;
                    $this->stager->log($scanId, 'error', $type.' aktarım hatası: '.$e->getMessage(), ['item_id'=>$item['id'], 'source_id'=>$item['source_id']]);
                }
            }
        }
        $this->stager->log($scanId, 'info', 'Import tamamlandı', $report);
        return $report;
    }

    private function importItem(string $type, array $item): string
    {
        $p = $item['payload'];
        return match($type) {
            'customers' => $this->importCustomer($item, $p),
            'products' => $this->importProduct($item, $p),
            'domain_extensions' => $this->importDomainExtension($item, $p),
            'hosting' => $this->importHosting($item, $p),
            'domains' => $this->importDomain($item, $p),
            'currencies' => $this->importCurrency($item, $p),
            default => $this->storeRaw($item, $type, $p)
        };
    }

    private function importCustomer(array $item, array $p): string
    {
        if (!$this->tableExists('customers')) { return $this->storeRaw($item,'customers',$p); }
        $email = $p['email'] ?? null;
        if (!$email) return 'skipped';
        $exists = $this->findExistingTarget('customers', $item, ['email'=>$email]);
        $name = trim(($p['firstname'] ?? '').' '.($p['lastname'] ?? '')) ?: ($p['companyname'] ?? $email);
        $data = ['name'=>$name,'email'=>$email,'phone'=>$p['phonenumber'] ?? null,'city'=>$p['city'] ?? null,'country'=>$p['country'] ?? null,'status'=>$p['status'] ?? 'active','source_type'=>'source','external_id'=>$item['source_id'],'source_id'=>$item['source_id']];
        if ($exists) {
            $this->safeUpdate('customers', $exists, $data);
            $this->map($item,'customers',$exists);
            return 'updated';
        }
        $id = $this->safeInsert('customers', $data);
        $this->map($item,'customers',$id);
        return 'imported';
    }

    private function importProduct(array $item, array $p): string
    {
        if (!$this->tableExists('products')) { return $this->storeRaw($item,'products',$p); }
        $name = $p['name'] ?? ('Ürün #'.$item['source_id']);
        $slug = $this->slug($name);
        $exists = $this->findExistingTarget('products', $item, ['slug'=>$slug]);
        $prices = $this->normalizeProductPrices($p['pricing'] ?? []);
        $mainCurrency = $prices['_main_currency'] ?? 'TRY';
        unset($prices['_main_currency']);
        $monthlyTry = $prices['monthly_try'] ?? $prices['monthly'] ?? 0;
        $data = [
            'name'=>$name,
            'slug'=>$slug,
            'type'=>$p['type'] ?? 'service',
            'module'=>$p['servertype'] ?? null,
            'description'=>$p['description'] ?? null,
            'price'=>$monthlyTry,
            'currency'=>'TRY',
            'currency_code'=>'TRY',
            'source_type'=>'source',
            'external_id'=>$item['source_id'],
            'source_id'=>$item['source_id']
        ];
        $data = array_merge($data, $prices);
        if ($exists) {
            $this->safeUpdate('products',$exists,$data);
            $this->upsertProductPricing($exists, $p, $item);
            $this->map($item,'products',$exists);
            return 'updated';
        }
        $id = $this->safeInsert('products',$data);
        $this->upsertProductPricing($id, $p, $item);
        $this->map($item,'products',$id);
        return 'imported';
    }

    private function importDomainExtension(array $item, array $p): string
    {
        $table = $this->tableExists('domain_pricing') ? 'domain_pricing' : ($this->tableExists('domain_extensions') ? 'domain_extensions' : null);
        if (!$table) { return $this->storeRaw($item,'domain_extensions',$p); }
        $ext = $p['extension'] ?? null;
        if (!$ext) return 'skipped';
        $prices = $this->normalizeDomainPrices($p['pricing'] ?? []);
        $mainCurrency = $prices['_main_currency'] ?? 'TRY';
        unset($prices['_main_currency']);
        $data = array_merge([
            'extension'=>$ext,
            'registrar'=>$p['autoreg'] ?? null,
            'epp_required'=>$p['eppcode'] ?? null,
            'price'=>$monthlyTry,
            'currency'=>'TRY',
            'currency_code'=>'TRY',
            'source_type'=>'source',
            'external_id'=>$item['source_id'],
            'source_id'=>$item['source_id']
        ], $prices);
        $exists = $this->findExistingTarget($table, $item, ['extension'=>$ext]);
        if ($exists) {
            $this->safeUpdate($table,$exists,$data);
            $this->map($item,$table,$exists);
            return 'updated';
        }
        $id = $this->safeInsert($table,$data);
        $this->map($item,$table,$id);
        return 'imported';
    }


    private function upsertProductPricing(int $productId, array $p, array $item): void
    {
        if ($productId <= 0 || !$this->tableExists('product_pricing')) return;
        $this->ensureTargetPricingColumns();
        $cycleMap = [
            'monthly' => ['price'=>'monthly','setup'=>'msetupfee'],
            'quarterly' => ['price'=>'quarterly','setup'=>'qsetupfee'],
            'semiannually' => ['price'=>'semiannually','setup'=>'ssetupfee'],
            'annually' => ['price'=>'annually','setup'=>'asetupfee'],
            'biennially' => ['price'=>'biennially','setup'=>'bsetupfee'],
            'triennially' => ['price'=>'triennially','setup'=>'tsetupfee'],
        ];
        foreach (($p['pricing'] ?? []) as $row) {
            $code = $this->currencyCode($row);
            $rate = $this->sellRate('USD');
            $margin = (float)($this->settings['margin_percent'] ?? $this->settings['profit_margin_percent'] ?? 0);
            foreach ($cycleMap as $cycle=>$map) {
                $price = $this->cleanPrice($row[$map['price']] ?? null);
                $setup = $this->cleanPrice($row[$map['setup']] ?? 0) ?? 0;
                if ($price === null && $setup <= 0) continue;
                [$priceUsd,$priceTry] = $this->dualCurrency((float)($price ?? 0), $code);
                [$setupUsd,$setupTry] = $this->dualCurrency((float)$setup, $code);
                $active = ($priceUsd > 0 || $priceTry > 0) ? 1 : 0;
                $data = [
                    'product_id'=>$productId,
                    'cycle'=>$cycle,
                    'price'=>$priceTry,
                    'setup_fee'=>$setupTry,
                    'currency'=>'TRY',
                    'price_usd'=>$priceUsd,
                    'price_try'=>$priceTry,
                    'setup_fee_usd'=>$setupUsd,
                    'setup_fee_try'=>$setupTry,
                    'base_currency'=>($code === 'USD' ? 'USD' : 'TRY'),
                    'exchange_rate'=>$rate,
                    'margin_percent'=>$margin,
                    'auto_convert'=>1,
                    'is_active'=>$active,
                    'source_type'=>'source',
                    'external_id'=>$item['source_id'],
                ];
                $data = $this->filterColumns('product_pricing', $data);
                if (!$data) continue;
                $cols = array_keys($data);
                $updates = [];
                foreach ($cols as $c) { if (!in_array($c, ['product_id','cycle'], true)) $updates[] = "`$c`=VALUES(`$c`)"; }
                $sql = "INSERT INTO product_pricing (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).") ON DUPLICATE KEY UPDATE ".implode(',', $updates);
                try { $stmt=$this->db->prepare($sql); $stmt->execute(array_values($data)); } catch (Throwable $e) {}
            }
        }
    }

    private function importHosting(array $item, array $p): string { return $this->storeRaw($item,'hosting',$p); }
    private function importDomain(array $item, array $p): string { return $this->storeRaw($item,'domains',$p); }
    private function importCurrency(array $item, array $p): string { return $this->storeRaw($item,'currencies',$p); }

    private function normalizeProductPrices(array $pricing): array
    {
        $out = [];
        $mainCurrency = 'TRY';
        $fields = [
            'msetupfee' => ['setup_fee','setup_fee'],
            'monthly' => ['monthly','monthly'],
            'quarterly' => ['quarterly','quarterly'],
            'semiannually' => ['semiannually','semiannual'],
            'annually' => ['annually','annual'],
            'biennially' => ['biennially','biennial'],
            'triennially' => ['triennially','triennial'],
        ];
        foreach ($pricing as $row) {
            $code = $this->currencyCode($row);
            if ($code) $mainCurrency = $code;
            foreach ($fields as $sourceField => $aliases) {
                if (!array_key_exists($sourceField, $row)) continue;
                $value = $this->cleanPrice($row[$sourceField]);
                if ($value === null) continue;
                [$usd, $try] = $this->dualCurrency($value, $code);
                foreach ($aliases as $alias) {
                    $out[$alias.'_usd'] = $usd;
                    $out[$alias.'_try'] = $try;
                    $out[$alias] = $code === 'TRY' ? $try : $usd;
                }
            }
        }
        $out['_main_currency'] = $mainCurrency;
        return $out;
    }

    private function normalizeDomainPrices(array $pricing): array
    {
        $out = [];
        $mainCurrency = 'TRY';
        $periodMap = [
            'msetupfee'=>'1y','qsetupfee'=>'2y','ssetupfee'=>'3y','asetupfee'=>'4y','bsetupfee'=>'5y',
            'monthly'=>'6y','quarterly'=>'7y','semiannually'=>'8y','annually'=>'9y','biennially'=>'10y','tsetupfee'=>'10y'
        ];
        foreach ($pricing as $type => $rows) {
            foreach ($rows as $row) {
                $prefix = match($type) {
                    'domainregister' => 'register',
                    'domaintransfer' => 'transfer',
                    'domainrenew' => 'renew',
                    default => $type
                };
                $code = $this->currencyCode($row);
                if ($code) $mainCurrency = $code;
                foreach ($periodMap as $field=>$label) {
                    if (!array_key_exists($field, $row)) continue;
                    $value = $this->cleanPrice($row[$field]);
                    if ($value === null) continue;
                    [$usd, $try] = $this->dualCurrency($value, $code);
                    $base = $prefix.'_'.$label;
                    $out[$base.'_usd'] = $usd;
                    $out[$base.'_try'] = $try;
                    $out[$base] = $code === 'TRY' ? $try : $usd;
                    // common aliases for first-year prices
                    if ($label === '1y') {
                        $out[$prefix.'_usd'] = $usd;
                        $out[$prefix.'_try'] = $try;
                        $out[$prefix] = $code === 'TRY' ? $try : $usd;
                    }
                }
            }
        }
        $out['_main_currency'] = $mainCurrency;
        return $out;
    }

    private function currencyCode(array $row): string
    {
        $code = strtoupper(trim((string)($row['currency_code'] ?? '')));
        if (!$code && isset($row['currency'])) $code = ((int)$row['currency'] === 1) ? 'TRY' : 'USD';
        return in_array($code, ['TL','TRL'], true) ? 'TRY' : ($code ?: 'TRY');
    }

    private function cleanPrice($value): ?float
    {
        if ($value === null || $value === '') return null;
        $v = (float)str_replace(',', '.', (string)$value);
        if ($v < 0) return null;
        return round($v, 6);
    }

    private function dualCurrency(float $value, string $sourceCurrency): array
    {
        $sourceCurrency = strtoupper($sourceCurrency ?: 'TRY');
        $sourceCurrency = $sourceCurrency === 'TL' ? 'TRY' : $sourceCurrency;
        if ($sourceCurrency === 'USD') {
            $usd = $value;
            $try = $value * $this->sellRate('USD');
        } elseif ($sourceCurrency === 'TRY') {
            $try = $value;
            $usd = $this->sellRate('USD') > 0 ? $value / $this->sellRate('USD') : 0;
        } else {
            $try = $value * $this->sellRate($sourceCurrency);
            $usd = $this->sellRate('USD') > 0 ? $try / $this->sellRate('USD') : 0;
        }
        return [round($usd, 2), round($try, 2)];
    }

    private function sellRate(string $currency): float
    {
        $currency = strtoupper($currency);
        $base = (float)($this->settings[strtolower($currency).'_try_rate'] ?? $this->settings['fallback_'.strtolower($currency).'_try'] ?? ($currency === 'USD' ? 40 : 1));
        if ($currency === 'TRY') return 1;
        $margin = (float)($this->settings['margin_percent'] ?? $this->settings['profit_margin_percent'] ?? 0);
        return $base * (1 + ($margin / 100));
    }

    private function loadSettings(): array
    {
        $defaults = [
            'target_currency' => 'TRY',
            'fallback_usd_try' => '40',
            'fallback_eur_try' => '43',
            'margin_percent' => '0',
        ];
        try {
            if ($this->tableExists('module_migration_bridge_settings')) {
                $rows = $this->db->query('SELECT setting_key, setting_value FROM module_migration_bridge_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
                return array_merge($defaults, $rows ?: []);
            }
        } catch (Throwable $e) {}
        return $defaults;
    }

    private function ensureTargetPricingColumns(): void
    {
        // Modül farklı Ahost One kurulumlarında çalışacağı için kolonları çalışma anında güvenli şekilde ekler.
        if ($this->tableExists('products')) {
            $cols = [];
            foreach (['setup_fee','one_time','monthly','quarterly','semiannual','semiannually','annual','annually','biennial','biennially','triennial','triennially'] as $base) {
                $cols[$base.'_usd'] = 'DECIMAL(14,2) NULL DEFAULT 0';
                $cols[$base.'_try'] = 'DECIMAL(14,2) NULL DEFAULT 0';
            }
            $cols += ['currency_code'=>'VARCHAR(10) NULL','source_type'=>'VARCHAR(40) NULL','external_id'=>'VARCHAR(80) NULL','source_id'=>'VARCHAR(80) NULL'];
            $this->addColumnsIfMissing('products', $cols);
        }
        foreach (['domain_pricing','domain_extensions'] as $table) {
            if (!$this->tableExists($table)) continue;
            $cols = [];
            foreach (['register','transfer','renew'] as $prefix) {
                foreach (['1y','2y','3y','4y','5y','6y','7y','8y','9y','10y'] as $period) {
                    $cols[$prefix.'_'.$period.'_usd'] = 'DECIMAL(14,2) NULL DEFAULT 0';
                    $cols[$prefix.'_'.$period.'_try'] = 'DECIMAL(14,2) NULL DEFAULT 0';
                }
                $cols[$prefix.'_usd'] = 'DECIMAL(14,2) NULL DEFAULT 0';
                $cols[$prefix.'_try'] = 'DECIMAL(14,2) NULL DEFAULT 0';
            }
            $cols += ['currency_code'=>'VARCHAR(10) NULL','source_type'=>'VARCHAR(40) NULL','external_id'=>'VARCHAR(80) NULL','source_id'=>'VARCHAR(80) NULL'];
            $this->addColumnsIfMissing($table, $cols);
        }
    }

    private function addColumnsIfMissing(string $table, array $columns): void
    {
        $existing = $this->columns($table);
        foreach ($columns as $column => $definition) {
            if (in_array($column, $existing, true)) continue;
            try { $this->db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition"); }
            catch (Throwable $e) { /* yetki yoksa modül filterColumns ile güvenli devam eder */ }
        }
    }

    private function storeRaw(array $item, string $type, array $payload): string
    {
        if (!$this->tableExists('module_migration_bridge_maps')) return 'skipped';
        $this->map($item, 'raw_'.$type, 0, $payload);
        return 'imported';
    }

    private function safeInsert(string $table, array $data): int
    {
        $data = $this->filterColumns($table, $data);
        if (!$data) return 0;
        $cols = array_keys($data);
        $sql = "INSERT INTO `$table` (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")";
        $stmt=$this->db->prepare($sql); $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    private function safeUpdate(string $table, int $id, array $data): int
    {
        $data = $this->filterColumns($table, $data);
        if (!$data) return $id;
        $sets = array_map(fn($c)=>"`$c`=?", array_keys($data));
        $stmt=$this->db->prepare("UPDATE `$table` SET ".implode(',',$sets)." WHERE id=?");
        $stmt->execute([...array_values($data), $id]);
        return $id;
    }

    private function filterColumns(string $table, array $data): array
    {
        $cols = $this->columns($table);
        return array_intersect_key($data, array_flip($cols));
    }

    private function columns(string $table): array
    {
        static $cache=[];
        if (!isset($cache[$table])) {
            $stmt=$this->db->query("SHOW COLUMNS FROM `$table`");
            $cache[$table]=array_column($stmt->fetchAll(PDO::FETCH_ASSOC),'Field');
        }
        return $cache[$table];
    }

    private function fetchId(string $table, string $field, string $value): ?int
    {
        if (!in_array($field, $this->columns($table), true)) return null;
        $stmt=$this->db->prepare("SELECT id FROM `$table` WHERE `$field`=? LIMIT 1"); $stmt->execute([$value]);
        $id=$stmt->fetchColumn(); return $id ? (int)$id : null;
    }

    private function findExistingTarget(string $table, array $item, array $fallbackFields = []): ?int
    {
        // 1) Önce bridge map: aynı Kaynak Sistem kaydı tekrar import edilirse mutlaka güncelle.
        if ($this->tableExists('module_migration_bridge_maps')) {
            $stmt=$this->db->prepare("SELECT target_id FROM module_migration_bridge_maps WHERE item_type=? AND source_id=? AND target_table=? LIMIT 1");
            $stmt->execute([$item['item_type'], $item['source_id'], $table]);
            $id=$stmt->fetchColumn();
            if ($id && (int)$id > 0) return (int)$id;
        }
        // 2) Sonra source/external id kolonları.
        foreach (['external_id','source_id'] as $field) {
            if (in_array($field, $this->columns($table), true)) {
                $stmt=$this->db->prepare("SELECT id FROM `$table` WHERE `$field`=? LIMIT 1");
                $stmt->execute([$item['source_id']]);
                $id=$stmt->fetchColumn();
                if ($id) return (int)$id;
            }
        }
        // 3) Son olarak doğal anahtarlar.
        foreach ($fallbackFields as $field=>$value) {
            if (!$value) continue;
            $id = $this->fetchId($table, $field, (string)$value);
            if ($id) return $id;
        }
        return null;
    }

    private function tableExists(string $table): bool
    {
        $stmt=$this->db->prepare('SHOW TABLES LIKE ?'); $stmt->execute([$table]); return (bool)$stmt->fetchColumn();
    }

    private function map(array $item, string $targetTable, int $targetId, array $extra=[]): void
    {
        if (!$this->tableExists('module_migration_bridge_maps')) return;
        $stmt=$this->db->prepare("INSERT INTO module_migration_bridge_maps (item_type,source_id,target_table,target_id,payload_hash) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE target_table=VALUES(target_table), target_id=VALUES(target_id), payload_hash=VALUES(payload_hash)");
        $stmt->execute([$item['item_type'],$item['source_id'],$targetTable,(string)$targetId,sha1(json_encode($item['payload'] ?? $extra, JSON_UNESCAPED_UNICODE))]);
    }

    private function slug(string $s): string
    {
        $s = mb_strtolower(trim($s), 'UTF-8');
        $map = ['ı'=>'i','ğ'=>'g','ü'=>'u','ş'=>'s','ö'=>'o','ç'=>'c'];
        $s = strtr($s,$map);
        $s = preg_replace('/[^a-z0-9]+/','-',$s);
        return trim($s,'-') ?: 'urun';
    }
}
