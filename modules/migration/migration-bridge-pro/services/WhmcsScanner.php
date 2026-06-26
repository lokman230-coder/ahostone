<?php
class WhmcsScanner
{
    private PDO $pdo;
    private array $currencyMap = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->currencyMap = $this->currencyMap();
    }

    public function scan(): array
    {
        return [
            'customers' => $this->customers(),
            'products' => $this->productsWithPricing(),
            'domain_extensions' => $this->domainExtensionPricing(),
            'hosting' => $this->hostingServices(),
            'domains' => $this->domains(),
            'invoices' => $this->invoices(),
            'tickets' => $this->tickets(),
            'currencies' => $this->currencies(),
        ];
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);
        return (bool)$stmt->fetchColumn();
    }

    private function rows(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function customers(): array
    {
        if (!$this->tableExists('tblclients')) return [];
        return $this->rows("SELECT id, firstname, lastname, companyname, email, phonenumber, country, city, status, datecreated FROM tblclients ORDER BY id DESC LIMIT 2000");
    }

    private function productsWithPricing(): array
    {
        if (!$this->tableExists('tblproducts')) return [];
        $products = $this->rows("SELECT p.id, p.gid, p.type, p.name, p.description, p.servertype, p.configoption1, p.paytype, p.hidden FROM tblproducts p ORDER BY p.gid, p.id");
        $pricing = $this->tableExists('tblpricing') ? $this->rows("SELECT * FROM tblpricing WHERE type='product'") : [];
        $byRel = [];
        foreach ($pricing as $row) {
            $row = $this->attachCurrency($row);
            $byRel[(string)$row['relid']][] = $row;
        }
        foreach ($products as &$p) {
            $p['source_type'] = 'source';
            $p['external_id'] = (string)$p['id'];
            $p['pricing'] = $byRel[(string)$p['id']] ?? [];
        }
        return $products;
    }

    private function domainExtensionPricing(): array
    {
        if (!$this->tableExists('tbldomainpricing')) return [];
        $extensions = $this->rows("SELECT id, extension, dnsmanagement, emailforwarding, idprotection, eppcode, autoreg FROM tbldomainpricing ORDER BY extension");
        $pricing = $this->tableExists('tblpricing') ? $this->rows("SELECT * FROM tblpricing WHERE type IN ('domainregister','domaintransfer','domainrenew')") : [];
        $byRel = [];
        foreach ($pricing as $row) {
            $row = $this->attachCurrency($row);
            $byRel[(string)$row['relid']][$row['type']][] = $row;
        }
        foreach ($extensions as &$e) {
            $e['source_type'] = 'source';
            $e['external_id'] = (string)$e['id'];
            $e['pricing'] = $byRel[(string)$e['id']] ?? [];
        }
        return $extensions;
    }

    private function hostingServices(): array
    {
        if (!$this->tableExists('tblhosting')) return [];
        return $this->rows("SELECT h.id, h.userid, h.packageid, h.domain, h.username, h.domainstatus, h.billingcycle, h.amount, h.firstpaymentamount, h.nextduedate, h.regdate, h.server FROM tblhosting h ORDER BY h.id DESC LIMIT 3000");
    }

    private function domains(): array
    {
        if (!$this->tableExists('tbldomains')) return [];
        return $this->rows("SELECT id, userid, domain, registrar, registrationdate, expirydate, nextduedate, status, recurringamount, firstpaymentamount, registrationperiod, dnsmanagement, emailforwarding, idprotection FROM tbldomains ORDER BY id DESC LIMIT 3000");
    }

    private function invoices(): array
    {
        if (!$this->tableExists('tblinvoices')) return [];
        return $this->rows("SELECT id, userid, date, duedate, total, subtotal, credit, tax, tax2, status, paymentmethod FROM tblinvoices ORDER BY id DESC LIMIT 3000");
    }

    private function tickets(): array
    {
        if (!$this->tableExists('tbltickets')) return [];
        return $this->rows("SELECT id, userid, tid, did, title, email, name, status, urgency, date, lastreply FROM tbltickets ORDER BY id DESC LIMIT 2000");
    }

    private function currencies(): array
    {
        if (!$this->tableExists('tblcurrencies')) return [];
        return $this->rows("SELECT id, code, prefix, suffix, rate FROM tblcurrencies ORDER BY id");
    }

    private function currencyMap(): array
    {
        $out = [];
        foreach ($this->currencies() as $c) {
            $id = (string)($c['id'] ?? '');
            if ($id === '') continue;
            $out[$id] = [
                'id' => (int)$c['id'],
                'code' => strtoupper((string)($c['code'] ?? '')),
                'rate' => (float)($c['rate'] ?? 1),
                'prefix' => $c['prefix'] ?? '',
                'suffix' => $c['suffix'] ?? '',
            ];
        }
        return $out;
    }

    private function attachCurrency(array $row): array
    {
        $cid = (string)($row['currency'] ?? '1');
        $c = $this->currencyMap[$cid] ?? ['id'=>(int)$cid, 'code'=>($cid === '1' ? 'TRY' : 'CUR'.$cid), 'rate'=>1, 'prefix'=>'', 'suffix'=>''];
        $row['currency_id'] = $c['id'];
        $row['currency_code'] = $c['code'] ?: ($cid === '1' ? 'TRY' : 'CUR'.$cid);
        $row['currency_rate'] = $c['rate'] ?: 1;
        $row['currency_prefix'] = $c['prefix'] ?? '';
        $row['currency_suffix'] = $c['suffix'] ?? '';
        return $row;
    }
}
