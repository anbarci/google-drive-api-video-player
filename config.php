<?php
/**
 * Google Drive API Video Player - Advanced Configuration
 * 
 * Çoklu API key desteği, admin panel ve subtitle sistemi için
 * gelişmiş yapılandırma dosyası
 * 
 * @author anbarci
 * @version 2.0.0
 */

// Hata raporlama (production'da kapatın)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Güvenlik başlıkları
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Proje bilgileri
define('PROJECT_NAME', 'Google Drive API Video Player');
define('PROJECT_VERSION', '2.0.0');
define('PROJECT_AUTHOR', 'anbarci');
define('PROJECT_URL', 'https://github.com/anbarci/google-drive-api-video-player');

// Dosya yolları
define('ROOT_PATH', __DIR__);
define('DATA_PATH', ROOT_PATH . '/data');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('API_PATH', ROOT_PATH . '/api');
define('PLAYER_PATH', ROOT_PATH . '/player');

// Veri dosyaları
define('VIDEOS_FILE', DATA_PATH . '/videos.json');
define('APIKEYS_FILE', DATA_PATH . '/apikeys.json');
define('SUBTITLES_FILE', DATA_PATH . '/subtitles.json');
define('SETTINGS_FILE', DATA_PATH . '/settings.json');
define('STATS_FILE', DATA_PATH . '/stats.json');

// Log dosyaları
define('API_LOG_FILE', LOGS_PATH . '/api.log');
define('ERROR_LOG_FILE', LOGS_PATH . '/error.log');
define('ACCESS_LOG_FILE', LOGS_PATH . '/access.log');
define('ADMIN_LOG_FILE', LOGS_PATH . '/admin.log');

// Admin ayarları
define('ADMIN_SETTINGS', [
    'username' => 'admin',
    'password' => password_hash('admin123', PASSWORD_DEFAULT), // Değiştirin!
    'session_timeout' => 3600, // 1 saat
    'max_login_attempts' => 5,
    'lockout_duration' => 900, // 15 dakika
    'require_https' => false, // Production'da true yapın
    'allowed_ips' => [], // Boş ise tüm IP'ler
]);

// Google Drive API ayarları
define('DRIVE_API_SETTINGS', [
    'base_url' => 'https://www.googleapis.com/drive/v3/files',
    'download_url' => 'https://www.googleapis.com/drive/v3/files/{FILE_ID}?alt=media',
    'info_url' => 'https://www.googleapis.com/drive/v3/files/{FILE_ID}',
    'thumbnail_url' => 'https://lh3.googleusercontent.com/d/{FILE_ID}',
    'quota_limit' => 100000000, // Günlük quota (100M)
    'rate_limit' => 1000, // Saatlik istek limit
    'timeout' => 30,
    'retry_attempts' => 3,
    'retry_delay' => 2, // saniye
]);

// Video player ayarları
define('PLAYER_SETTINGS', [
    'default_player' => 'plyr',
    'supported_players' => [
        'plyr' => [
            'name' => 'Plyr Player',
            'description' => 'Modern HTML5 video player',
            'icon' => 'fas fa-play-circle',
            'enabled' => true
        ],
        'videojs' => [
            'name' => 'Video.js Player',
            'description' => 'Profesyonel video player',
            'icon' => 'fas fa-video',
            'enabled' => true
        ],
        'jwplayer' => [
            'name' => 'JW Player',
            'description' => 'Gelişmiş video player',
            'icon' => 'fas fa-film',
            'enabled' => true
        ],
        'html5' => [
            'name' => 'HTML5 Player',
            'description' => 'Basit HTML5 player',
            'icon' => 'fas fa-play',
            'enabled' => true
        ]
    ],
    'embed_settings' => [
        'show_title' => false, // Video başlığını göster
        'show_description' => false, // Açıklamayı göster
        'show_controls' => true, // Kontrolleri göster
        'autoplay' => false, // Otomatik oynat
        'muted' => false, // Sessiz başlat
        'loop' => false, // Döngü
        'responsive' => true, // Responsive tasarım
        'quality_selector' => true, // Kalite seçici
        'speed_selector' => true, // Hız seçici
        'fullscreen' => true, // Tam ekran
        'picture_in_picture' => true, // Resim içinde resim
    ]
]);

// Subtitle ayarları
define('SUBTITLE_SETTINGS', [
    'supported_formats' => ['srt', 'vtt', 'ass', 'ssa'],
    'supported_languages' => [
        'tr' => 'Türkçe',
        'en' => 'English',
        'es' => 'Español',
        'fr' => 'Français',
        'de' => 'Deutsch',
        'it' => 'Italiano',
        'pt' => 'Português',
        'ru' => 'Русский',
        'ar' => 'العربية',
        'zh' => '中文',
        'ja' => '日本語',
        'ko' => '한국어'
    ],
    'default_language' => 'tr',
    'auto_detect' => true, // Otomatik subtitle tespit
    'cache_duration' => 3600, // 1 saat
    'max_file_size' => 1048576, // 1MB
]);

// Cache ayarları
define('CACHE_SETTINGS', [
    'enabled' => true,
    'duration' => [
        'video_info' => 3600, // 1 saat
        'api_response' => 1800, // 30 dakika
        'subtitle' => 7200, // 2 saat
        'thumbnail' => 86400, // 1 gün
    ],
    'directory' => ROOT_PATH . '/cache',
    'max_size' => 104857600, // 100MB
    'cleanup_interval' => 3600, // 1 saat
]);

// Güvenlik ayarları
define('SECURITY_SETTINGS', [
    'encryption_key' => 'your-32-character-secret-key-here!', // Değiştirin!
    'api_key_encryption' => true,
    'csrf_protection' => true,
    'rate_limiting' => [
        'enabled' => true,
        'requests_per_minute' => 60,
        'requests_per_hour' => 1000,
        'requests_per_day' => 10000,
    ],
    'ip_whitelist' => [], // Boş ise tüm IP'ler
    'ip_blacklist' => [],
    'user_agent_filtering' => true,
    'referrer_policy' => 'strict-origin-when-cross-origin',
    'content_security_policy' => true,
]);

// Log ayarları
define('LOG_SETTINGS', [
    'enabled' => true,
    'level' => 'INFO', // DEBUG, INFO, WARNING, ERROR
    'max_file_size' => 10485760, // 10MB
    'max_files' => 5,
    'rotate' => true,
    'format' => '[%datetime%] %level%: %message% %context%',
    'log_api_requests' => true,
    'log_admin_actions' => true,
    'log_errors' => true,
    'log_access' => false, // Açmak için true
]);

// Performans ayarları
define('PERFORMANCE_SETTINGS', [
    'enable_gzip' => true,
    'enable_caching' => true,
    'minify_output' => false, // Production'da true
    'optimize_images' => true,
    'lazy_loading' => true,
    'preload_critical' => true,
    'cdn_enabled' => false,
    'cdn_url' => '',
]);

// Database ayarları (gelecekteki geliştirmeler için)
define('DATABASE_SETTINGS', [
    'enabled' => false,
    'type' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'drive_player',
    'username' => '',
    'password' => '',
    'charset' => 'utf8mb4',
    'prefix' => 'dp_',
]);

// ====================
// UTILITY FUNCTIONS
// ====================

/**
 * API key şifreleme
 */
function encryptApiKey($apiKey) {
    if (!SECURITY_SETTINGS['api_key_encryption']) {
        return $apiKey;
    }
    
    $key = SECURITY_SETTINGS['encryption_key'];
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($apiKey, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * API key çözme
 */
function decryptApiKey($encryptedApiKey) {
    if (!SECURITY_SETTINGS['api_key_encryption']) {
        return $encryptedApiKey;
    }
    
    $key = SECURITY_SETTINGS['encryption_key'];
    $data = base64_decode($encryptedApiKey);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}

/**
 * Google Drive video ID çıkarma
 */
function extractDriveVideoId($url) {
    $patterns = [
        '/\/file\/d\/([a-zA-Z0-9_-]+)/',
        '/[?&]id=([a-zA-Z0-9_-]+)/',
        '/\/open\?id=([a-zA-Z0-9_-]+)/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Video ID validasyonu
 */
function validateVideoId($videoId) {
    return preg_match('/^[a-zA-Z0-9_-]{10,50}$/', $videoId);
}

/**
 * API key validasyonu
 */
function validateApiKey($apiKey) {
    return preg_match('/^[a-zA-Z0-9_-]{39}$/', $apiKey);
}

/**
 * Güvenli HTML çıktısı
 */
function safeOutput($text, $allowTags = false) {
    if ($allowTags) {
        return strip_tags($text, '<b><i><u><strong><em><br><p>');
    }
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * JSON dosya okuma
 */
function readJsonFile($filename, $default = []) {
    if (!file_exists($filename)) {
        return $default;
    }
    
    $content = file_get_contents($filename);
    $data = json_decode($content, true);
    
    return $data !== null ? $data : $default;
}

/**
 * JSON dosya yazma
 */
function writeJsonFile($filename, $data) {
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($filename, $json, LOCK_EX) !== false;
}

/**
 * Log yazma
 */
function writeLog($level, $message, $context = [], $file = null) {
    if (!LOG_SETTINGS['enabled']) {
        return;
    }
    
    $levels = ['DEBUG' => 0, 'INFO' => 1, 'WARNING' => 2, 'ERROR' => 3];
    $currentLevel = $levels[LOG_SETTINGS['level']] ?? 1;
    $messageLevel = $levels[$level] ?? 1;
    
    if ($messageLevel < $currentLevel) {
        return;
    }
    
    $logFile = $file ?: ERROR_LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $logMessage = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;
    
    // Log dizinini oluştur
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Dosya boyutu kontrolü
    if (file_exists($logFile) && filesize($logFile) > LOG_SETTINGS['max_file_size']) {
        if (LOG_SETTINGS['rotate']) {
            $backupFile = $logFile . '.' . date('Y-m-d-H-i-s');
            rename($logFile, $backupFile);
            
            // Eski log dosyalarını temizle
            $logFiles = glob($logFile . '.*');
            if (count($logFiles) > LOG_SETTINGS['max_files']) {
                $oldFiles = array_slice($logFiles, 0, count($logFiles) - LOG_SETTINGS['max_files']);
                foreach ($oldFiles as $oldFile) {
                    unlink($oldFile);
                }
            }
        } else {
            file_put_contents($logFile, '');
        }
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Rate limiting kontrolü
 */
function checkRateLimit($identifier = null) {
    if (!SECURITY_SETTINGS['rate_limiting']['enabled']) {
        return true;
    }
    
    $identifier = $identifier ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $rateLimitFile = CACHE_SETTINGS['directory'] . '/rate_limit_' . md5($identifier) . '.json';
    
    $now = time();
    $limits = SECURITY_SETTINGS['rate_limiting'];
    
    // Mevcut rate limit verisini oku
    $rateData = readJsonFile($rateLimitFile, [
        'minute' => ['count' => 0, 'reset' => $now + 60],
        'hour' => ['count' => 0, 'reset' => $now + 3600],
        'day' => ['count' => 0, 'reset' => $now + 86400]
    ]);
    
    // Süresi dolmuş verileri sıfırla
    foreach ($rateData as $period => $data) {
        if ($now > $data['reset']) {
            $rateData[$period] = [
                'count' => 0,
                'reset' => $now + ($period === 'minute' ? 60 : ($period === 'hour' ? 3600 : 86400))
            ];
        }
    }
    
    // Limit kontrolleri
    if ($rateData['minute']['count'] >= $limits['requests_per_minute'] ||
        $rateData['hour']['count'] >= $limits['requests_per_hour'] ||
        $rateData['day']['count'] >= $limits['requests_per_day']) {
        
        writeLog('WARNING', 'Rate limit exceeded', [
            'ip' => $identifier,
            'minute_count' => $rateData['minute']['count'],
            'hour_count' => $rateData['hour']['count'],
            'day_count' => $rateData['day']['count']
        ]);
        
        return false;
    }
    
    // Sayıları artır
    $rateData['minute']['count']++;
    $rateData['hour']['count']++;
    $rateData['day']['count']++;
    
    // Veriyi kaydet
    writeJsonFile($rateLimitFile, $rateData);
    
    return true;
}

/**
 * CSRF token oluşturma
 */
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrulama
 */
function validateCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * İlk kurulum
 */
function initializeApp() {
    $directories = [
        DATA_PATH,
        LOGS_PATH,
        CACHE_SETTINGS['directory']
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    // Varsayılan veri dosyalarını oluştur
    if (!file_exists(VIDEOS_FILE)) {
        writeJsonFile(VIDEOS_FILE, []);
    }
    
    if (!file_exists(APIKEYS_FILE)) {
        writeJsonFile(APIKEYS_FILE, []);
    }
    
    if (!file_exists(SUBTITLES_FILE)) {
        writeJsonFile(SUBTITLES_FILE, []);
    }
    
    if (!file_exists(SETTINGS_FILE)) {
        writeJsonFile(SETTINGS_FILE, [
            'app_name' => PROJECT_NAME,
            'app_version' => PROJECT_VERSION,
            'installed_at' => date('Y-m-d H:i:s'),
            'theme' => 'dark',
            'language' => 'tr'
        ]);
    }
    
    if (!file_exists(STATS_FILE)) {
        writeJsonFile(STATS_FILE, [
            'total_videos' => 0,
            'total_views' => 0,
            'api_requests' => 0,
            'last_updated' => date('Y-m-d H:i:s')
        ]);
    }
}

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rate limiting kontrolü
if (!checkRateLimit()) {
    http_response_code(429);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Too Many Requests',
        'message' => 'Rate limit exceeded. Please try again later.',
        'retry_after' => 60
    ]);
    exit;
}

// İlk kurulum
initializeApp();

// Uygulama başlatıldı logu
writeLog('INFO', 'Application initialized', [
    'version' => PROJECT_VERSION,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? '/'
]);
?>