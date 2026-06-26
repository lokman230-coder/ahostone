<?php
require_once __DIR__.'/../services/WhmcsScanner.php';
require_once __DIR__.'/../services/MigrationStager.php';
require_once __DIR__.'/../services/AhostImporter.php';

class MigrationBridgeProController
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: $this->detectAppPdo();
    }

    public function index(): void
    {
        $this->view('index', ['title'=>'Migration Bridge Pro']);
    }

    public function connect(): void
    {
        $pdo = $this->sourcePdo($_POST);
        $scanner = new WhmcsScanner($pdo);
        $data = $scanner->scan();
        $stager = new MigrationStager($this->db);
        $scanId = $stager->createScan(null, $_POST['source_type'] ?? 'Kaynak Sistem', $data);
        $this->redirect('/admin/modules/migration-bridge-pro/scan/'.$scanId);
    }

    public function scan(): void
    {
        $this->connect();
    }

    public function preview($id = null): void
    {
        $scanId = (int)($id ?? ($_GET['id'] ?? 0));
        $stager = new MigrationStager($this->db);
        $preview = $stager->getPreview($scanId);
        $this->view('preview', ['scanId'=>$scanId, 'preview'=>$preview]);
    }

    public function import(): void
    {
        $scanId = (int)($_POST['scan_id'] ?? 0);
        $actions = $_POST['actions'] ?? [];
        $stager = new MigrationStager($this->db);
        $stager->updateActions($scanId, $actions);
        $importer = new AhostImporter($this->db, $stager);
        $report = $importer->importScan($scanId);
        $this->view('report', ['scanId'=>$scanId, 'report'=>$report]);
    }

    public function upload(): void
    {
        $error = null;
        $files = [];
        if (empty($_FILES['migration_file']['tmp_name'])) {
            $error = 'Dosya gelmedi. Form enctype multipart/form-data ve input name="migration_file" olmalı.';
        } else {
            $name = $_FILES['migration_file']['name'];
            $tmp = $_FILES['migration_file']['tmp_name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ext === 'zip') {
                if (!class_exists('ZipArchive')) $error = 'PHP ZipArchive eklentisi aktif değil.';
                else {
                    $zip = new ZipArchive();
                    if ($zip->open($tmp) === true) {
                        for ($i=0; $i<$zip->numFiles; $i++) {
                            $n = $zip->getNameIndex($i);
                            if (preg_match('/\.(sql|gz)$/i', $n)) $files[] = $n;
                        }
                        $zip->close();
                    } else $error = 'ZIP dosyası açılamadı.';
                }
            } elseif (in_array($ext, ['sql','gz'], true)) {
                $files[] = $name;
            } else $error = 'Sadece .sql, .zip veya .gz dosyası kabul edilir.';
        }
        $this->view('upload_result', ['error'=>$error, 'files'=>$files]);
    }

    public function logs(): void
    {
        $rows = $this->db->query('SELECT * FROM module_migration_bridge_logs ORDER BY id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        $this->view('logs', ['logs'=>$rows]);
    }

    private function sourcePdo(array $input): PDO
    {
        $host = $input['host'] ?? '127.0.0.1';
        $port = (int)($input['port'] ?? 3306);
        $db = $input['database'] ?? '';
        $user = $input['username'] ?? '';
        $pass = $input['password'] ?? '';
        $charset = $input['charset'] ?? 'utf8mb4';
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    }

    private function detectAppPdo(): PDO
    {
        global $pdo, $db, $database;
        if ($pdo instanceof PDO) return $pdo;
        if ($db instanceof PDO) return $db;
        if ($database instanceof PDO) return $database;
        if (defined('DB_HOST')) {
            return new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        }
        throw new RuntimeException('Ahost One hedef PDO bağlantısı bulunamadı. Controller constructor içine PDO gönderin.');
    }

    private function view(string $name, array $data=[]): void
    {
        extract($data);
        $view = __DIR__.'/../views/'.$name.'.php';
        if (!is_file($view)) throw new RuntimeException('View bulunamadı: '.$name);
        include $view;
    }

    private function redirect(string $url): void
    {
        if (!headers_sent()) { header('Location: '.$url); exit; }
        echo '<script>location.href='.json_encode($url).'</script>';
        exit;
    }
}
