<?php
/**
 * Ahost One v25.0.0 RC9 - PHP Screenshot Bridge
 *
 * PHP tek başına CSS/JS render edemez. Bu bridge Visual Scan için motor seçer:
 * - Local Chrome/Chromium: PHP exec ile gerçek PNG ekran görüntüsü
 * - Remote Screenshot API: cURL ile uzak tarayıcı/screenshot servisi
 * - Basic fallback: gerçek screenshot yoksa SVG placeholder + route/sistem raporu
 */
class PHPScreenshotBridge
{
    public static function config(): array
    {
        $setting = function_exists('admin_setting') ? 'admin_setting' : null;
        $get = function(string $key, $default='') use ($setting) {
            if ($setting) return (string)admin_setting($key, $default);
            return $default;
        };
        return [
            'engine' => $get('qa_screenshot_engine', 'auto'), // auto, local_chrome, remote_api, basic
            'chrome_path' => $get('qa_screenshot_chrome_path', ''),
            'remote_endpoint' => $get('qa_screenshot_remote_endpoint', ''),
            'remote_token' => $get('qa_screenshot_remote_token', ''),
            'wait_ms' => max(300, (int)$get('qa_screenshot_wait_ms', '2500')),
            'timeout' => max(5, (int)$get('qa_screenshot_timeout', '12')),
            'full_page' => $get('qa_screenshot_full_page', '1') !== '0',
            'desktop_width' => max(320, (int)$get('qa_screenshot_desktop_width', '1440')),
            'desktop_height' => max(480, (int)$get('qa_screenshot_desktop_height', '1200')),
            'mobile_width' => max(320, (int)$get('qa_screenshot_mobile_width', '390')),
            'mobile_height' => max(480, (int)$get('qa_screenshot_mobile_height', '844')),
        ];
    }

    public static function status(): array
    {
        $cfg = self::config();
        $chrome = self::findChrome($cfg['chrome_path']);
        $execEnabled = self::execEnabled();
        $remoteConfigured = trim($cfg['remote_endpoint']) !== '';
        $recommended = 'basic';
        if ($execEnabled && $chrome) $recommended = 'local_chrome';
        elseif ($remoteConfigured) $recommended = 'remote_api';
        return [
            'configured_engine' => $cfg['engine'],
            'recommended_engine' => $recommended,
            'exec_enabled' => $execEnabled,
            'local_chrome_path' => $chrome ?: '',
            'remote_configured' => $remoteConfigured,
            'remote_endpoint' => $cfg['remote_endpoint'],
            'wait_ms' => $cfg['wait_ms'],
            'timeout' => $cfg['timeout'],
            'desktop' => $cfg['desktop_width'].'x'.$cfg['desktop_height'],
            'mobile' => $cfg['mobile_width'].'x'.$cfg['mobile_height'],
        ];
    }

    public static function captureTo(string $url, string $folder, string $slug, string $viewport, int $width, int $height, string $label=''): array
    {
        if (!is_dir($folder)) @mkdir($folder, 0775, true);
        $cfg = self::config();
        $engine = $cfg['engine'];
        $targetPng = rtrim($folder, '/').'/'.$slug.'.png';
        $targetSvg = rtrim($folder, '/').'/'.$slug.'.svg';
        $tries = [];

        if ($engine === 'auto' || $engine === 'local_chrome') {
            $result = self::captureWithLocalChrome($url, $targetPng, $width, $height, $cfg);
            $tries[] = $result['message'] ?? 'local_chrome denendi';
            if (!empty($result['success'])) {
                return [
                    'success'=>true, 'real'=>true, 'engine'=>'local_chrome', 'file'=>$targetPng,
                    'relative'=>basename($folder).'/'.basename($targetPng), 'message'=>$result['message'] ?? 'Local Chrome screenshot üretildi.'
                ];
            }
            if ($engine === 'local_chrome') {
                self::writePlaceholderSvg($targetSvg, $label ?: $url, $viewport, $width, $height, 'Local Chrome başarısız: '.($result['message'] ?? 'bilinmiyor'));
                return ['success'=>false,'real'=>false,'engine'=>'fallback_svg','file'=>$targetSvg,'relative'=>basename($folder).'/'.basename($targetSvg),'message'=>implode(' | ', $tries)];
            }
        }

        if ($engine === 'auto' || $engine === 'remote_api') {
            $result = self::captureWithRemoteApi($url, $targetPng, $width, $height, $cfg);
            $tries[] = $result['message'] ?? 'remote_api denendi';
            if (!empty($result['success'])) {
                return [
                    'success'=>true, 'real'=>true, 'engine'=>'remote_api', 'file'=>$targetPng,
                    'relative'=>basename($folder).'/'.basename($targetPng), 'message'=>$result['message'] ?? 'Remote API screenshot üretildi.'
                ];
            }
            if ($engine === 'remote_api') {
                self::writePlaceholderSvg($targetSvg, $label ?: $url, $viewport, $width, $height, 'Remote API başarısız: '.($result['message'] ?? 'bilinmiyor'));
                return ['success'=>false,'real'=>false,'engine'=>'fallback_svg','file'=>$targetSvg,'relative'=>basename($folder).'/'.basename($targetSvg),'message'=>implode(' | ', $tries)];
            }
        }

        self::writePlaceholderSvg($targetSvg, $label ?: $url, $viewport, $width, $height, 'Basic fallback: gerçek screenshot için Local Chrome veya Remote Screenshot API gerekir.');
        return ['success'=>false,'real'=>false,'engine'=>'fallback_svg','file'=>$targetSvg,'relative'=>basename($folder).'/'.basename($targetSvg),'message'=>implode(' | ', $tries) ?: 'Basic fallback kullanıldı.'];
    }

    public static function captureWithLocalChrome(string $url, string $target, int $width, int $height, array $cfg): array
    {
        static $localDisabledForThisRequest = false;
        if ($localDisabledForThisRequest) return ['success'=>false, 'message'=>'Local Chrome önceki denemede başarısız olduğu için bu taramada atlandı.'];
        if (!self::execEnabled()) return ['success'=>false, 'message'=>'PHP exec/shell_exec/proc_open kapalı.'];
        $chrome = self::findChrome($cfg['chrome_path'] ?? '');
        if (!$chrome) return ['success'=>false, 'message'=>'Sunucuda Chrome/Chromium bulunamadı.'];
        if (is_file($target)) @unlink($target);
        $common = [
            escapeshellarg($chrome),
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--hide-scrollbars',
            '--ignore-certificate-errors',
            '--run-all-compositor-stages-before-draw',
            '--window-size='.(int)$width.','.(int)$height,
            '--virtual-time-budget='.(int)($cfg['wait_ms'] ?? 2500),
            '--screenshot='.escapeshellarg($target),
            escapeshellarg($url),
            '2>&1'
        ];
        $cmd = self::withShellTimeout(implode(' ', $common), (int)($cfg['timeout'] ?? 12));
        $out = [];
        $code = 0;
        @exec($cmd, $out, $code);
        if (is_file($target) && filesize($target) > 1000) return ['success'=>true, 'message'=>'Local Chrome screenshot OK.'];

        // Eski Chromium sürümleri --headless=new desteklemeyebilir.
        $cmd2 = str_replace('--headless=new', '--headless', $cmd);
        $out2 = [];
        $code2 = 0;
        @exec($cmd2, $out2, $code2);
        if (is_file($target) && filesize($target) > 1000) return ['success'=>true, 'message'=>'Local Chrome legacy headless screenshot OK.'];

        $msg = trim(implode(' ', array_slice(array_merge($out, $out2), 0, 8)));
        $localDisabledForThisRequest = true;
        return ['success'=>false, 'message'=>$msg ?: 'Chrome komutu çalıştı ama PNG üretilemedi.'];
    }

    public static function captureWithRemoteApi(string $url, string $target, int $width, int $height, array $cfg): array
    {
        $endpoint = trim((string)($cfg['remote_endpoint'] ?? ''));
        if ($endpoint === '') return ['success'=>false, 'message'=>'Remote endpoint ayarlı değil.'];
        if (!function_exists('curl_init')) return ['success'=>false, 'message'=>'cURL extension kapalı.'];
        $payload = json_encode([
            'url'=>$url,
            'width'=>$width,
            'height'=>$height,
            'full_page'=>!empty($cfg['full_page']),
            'wait_ms'=>(int)($cfg['wait_ms'] ?? 2500),
            'token'=>(string)($cfg['remote_token'] ?? ''),
        ], JSON_UNESCAPED_UNICODE);
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST=>true,
            CURLOPT_POSTFIELDS=>$payload,
            CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: image/png,application/json,*/*'],
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_FOLLOWLOCATION=>true,
            CURLOPT_TIMEOUT=>(int)($cfg['timeout'] ?? 12),
        ]);
        if (!empty($cfg['remote_token'])) curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Accept: image/png,application/json,*/*','Authorization: Bearer '.$cfg['remote_token']]);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $ctype = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $status >= 400) return ['success'=>false,'message'=>'Remote API hata: HTTP '.$status.' '.$err];
        if (str_contains($ctype, 'image/png') || str_starts_with((string)$body, "\x89PNG")) {
            file_put_contents($target, $body);
            return ['success'=>is_file($target) && filesize($target) > 1000, 'message'=>'Remote API PNG döndürdü.'];
        }
        $json = json_decode((string)$body, true);
        if (is_array($json) && !empty($json['image_base64'])) {
            $raw = base64_decode((string)$json['image_base64'], true);
            if ($raw !== false) {
                file_put_contents($target, $raw);
                return ['success'=>is_file($target) && filesize($target) > 1000, 'message'=>'Remote API base64 PNG döndürdü.'];
            }
        }
        return ['success'=>false,'message'=>'Remote API desteklenen PNG/base64 formatı döndürmedi.'];
    }

    private static function withShellTimeout(string $cmd, int $seconds): string
    {
        $seconds = max(3, min(180, $seconds));
        if (stripos(PHP_OS, 'WIN') === 0) return $cmd;
        if (self::execEnabled()) {
            $timeout = trim((string)@shell_exec('command -v timeout 2>/dev/null'));
            if ($timeout !== '') return escapeshellarg($timeout).' '.(int)$seconds.'s '.$cmd;
        }
        return $cmd;
    }

    public static function findChrome(string $preferred=''): string
    {
        $preferred = trim($preferred);
        if ($preferred !== '' && is_file($preferred)) return $preferred;
        $candidates = [
            '/usr/bin/google-chrome','/usr/bin/google-chrome-stable','/usr/bin/chromium','/usr/bin/chromium-browser',
            '/snap/bin/chromium','/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        ];
        foreach ($candidates as $c) if (is_file($c)) return $c;
        if (self::execEnabled()) {
            foreach (['google-chrome','google-chrome-stable','chromium','chromium-browser','chrome'] as $bin) {
                $cmd = stripos(PHP_OS, 'WIN') === 0 ? 'where '.escapeshellarg($bin).' 2>NUL' : 'command -v '.escapeshellarg($bin).' 2>/dev/null';
                $out = trim((string)@shell_exec($cmd));
                if ($out !== '') {
                    $first = strtok($out, "\r\n");
                    if ($first && is_file($first)) return $first;
                }
            }
        }
        return '';
    }

    public static function execEnabled(): bool
    {
        $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
        foreach (['exec','shell_exec'] as $fn) {
            if (!function_exists($fn) || in_array($fn, $disabled, true)) return false;
        }
        return true;
    }

    public static function writePlaceholderSvg(string $file, string $title, string $viewport, int $width, int $height, string $message): void
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES|ENT_XML1, 'UTF-8');
        $safeViewport = htmlspecialchars($viewport.' '.$width.'x'.$height, ENT_QUOTES|ENT_XML1, 'UTF-8');
        $safeMessage = htmlspecialchars($message, ENT_QUOTES|ENT_XML1, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="960" height="600" viewBox="0 0 960 600"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#0f172a"/><stop offset="1" stop-color="#2563eb"/></linearGradient></defs><rect width="960" height="600" fill="#f8fafc"/><rect x="24" y="24" width="912" height="92" rx="24" fill="url(#g)"/><text x="58" y="80" fill="#fff" font-family="Arial" font-size="32" font-weight="800">'.$safeTitle.'</text><rect x="58" y="150" width="250" height="160" rx="22" fill="#fff" stroke="#e5e7eb"/><text x="82" y="202" fill="#64748b" font-family="Arial" font-size="20" font-weight="700">Viewport</text><text x="82" y="250" fill="#0f172a" font-family="Arial" font-size="30" font-weight="800">'.$safeViewport.'</text><rect x="340" y="150" width="520" height="160" rx="22" fill="#fff" stroke="#e5e7eb"/><text x="364" y="202" fill="#64748b" font-family="Arial" font-size="20" font-weight="700">Screenshot Motoru</text><text x="364" y="250" fill="#0f172a" font-family="Arial" font-size="30" font-weight="800">PHP Screenshot Bridge</text><rect x="58" y="350" width="844" height="150" rx="22" fill="#fff" stroke="#e5e7eb"/><text x="82" y="406" fill="#0f172a" font-family="Arial" font-size="25" font-weight="800">Basic fallback placeholder</text><text x="82" y="456" fill="#64748b" font-family="Arial" font-size="18">'.$safeMessage.'</text></svg>';
        file_put_contents($file, $svg);
    }
}
