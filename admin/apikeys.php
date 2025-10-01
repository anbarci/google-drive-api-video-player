<?php
/**
 * Admin Panel - API Key Yönetimi
 */

require_once __DIR__ . '/../config.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = 'info';

// API key ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Güvenlik hatası!';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'];
        
        if ($action === 'add_key') {
            $keyName = trim($_POST['key_name'] ?? '');
            $apiKey = trim($_POST['api_key'] ?? '');
            $dailyQuota = intval($_POST['daily_quota'] ?? 100000000);
            
            if (empty($keyName) || empty($apiKey)) {
                $message = 'Key adı ve API key gerekli!';
                $messageType = 'danger';
            } elseif (!validateApiKey($apiKey)) {
                $message = 'Geçersiz API key formatı!';
                $messageType = 'danger';
            } else {
                $apiKeys = readJsonFile(APIKEYS_FILE, []);
                
                // Yeni key ekle
                $newKey = [
                    'id' => uniqid('key_', true),
                    'name' => $keyName,
                    'encrypted_key' => encryptApiKey($apiKey),
                    'daily_quota' => $dailyQuota,
                    'requests_today' => 0,
                    'enabled' => true,
                    'blocked' => false,
                    'created_at' => date('Y-m-d H:i:s'),
                    'last_used' => null,
                    'error_count' => 0,
                    'blocked_until' => null
                ];
                
                $apiKeys[] = $newKey;
                
                if (writeJsonFile(APIKEYS_FILE, $apiKeys)) {
                    $message = 'API key başarıyla eklendi!';
                    $messageType = 'success';
                    
                    writeLog('INFO', 'API key added', ['key_name' => $keyName], ADMIN_LOG_FILE);
                } else {
                    $message = 'API key kaydedilemedi!';
                    $messageType = 'danger';
                }
            }
        } elseif ($action === 'toggle_key') {
            $keyId = $_POST['key_id'] ?? '';
            $apiKeys = readJsonFile(APIKEYS_FILE, []);
            
            foreach ($apiKeys as &$key) {
                if ($key['id'] === $keyId) {
                    $key['enabled'] = !$key['enabled'];
                    $message = 'API key durumu güncellendi!';
                    $messageType = 'success';
                    break;
                }
            }
            
            writeJsonFile(APIKEYS_FILE, $apiKeys);
        } elseif ($action === 'delete_key') {
            $keyId = $_POST['key_id'] ?? '';
            $apiKeys = readJsonFile(APIKEYS_FILE, []);
            
            $apiKeys = array_filter($apiKeys, function($key) use ($keyId) {
                return $key['id'] !== $keyId;
            });
            
            if (writeJsonFile(APIKEYS_FILE, array_values($apiKeys))) {
                $message = 'API key silindi!';
                $messageType = 'success';
                
                writeLog('INFO', 'API key deleted', ['key_id' => $keyId], ADMIN_LOG_FILE);
            }
        }
    }
}

$apiKeys = readJsonFile(APIKEYS_FILE, []);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Key Yönetimi - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-video"></i> Admin Panel
            </a>
            <div>
                <a href="index.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-dashboard"></i> Dashboard
                </a>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt"></i> Çıkış
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-key"></i> API Key Yönetimi</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKeyModal">
                        <i class="fas fa-plus"></i> Yeni API Key
                    </button>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($apiKeys)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                            <h5>API Key Bulunamadı</h5>
                            <p class="text-muted">Henüz hiç API key eklenmemiş. İlk API key'inizi eklemek için yukarıdaki butona tıklayın.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ad</th>
                                        <th>Oluşturma</th>
                                        <th>Son Kullanım</th>
                                        <th>Günlük Kullanım</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($apiKeys as $key): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($key['name']) ?></strong>
                                            <br><small class="text-muted">ID: <?= htmlspecialchars(substr($key['id'], 0, 8)) ?>...</small>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($key['created_at'])) ?></td>
                                        <td>
                                            <?php if ($key['last_used']): ?>
                                                <?= date('d.m.Y H:i', strtotime($key['last_used'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Kullanılmadı</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= number_format($key['requests_today']) ?> / <?= number_format($key['daily_quota']) ?>
                                            <div class="progress mt-1" style="height: 5px;">
                                                <?php $percentage = ($key['requests_today'] / $key['daily_quota']) * 100; ?>
                                                <div class="progress-bar" style="width: <?= min($percentage, 100) ?>%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($key['enabled']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pasif</span>
                                            <?php endif; ?>
                                            
                                            <?php if ($key['blocked_until'] && time() < strtotime($key['blocked_until'])): ?>
                                                <br><span class="badge bg-danger">Bloklu</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                <input type="hidden" name="action" value="toggle_key">
                                                <input type="hidden" name="key_id" value="<?= $key['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-<?= $key['enabled'] ? 'warning' : 'success' ?>">
                                                    <i class="fas fa-<?= $key['enabled'] ? 'pause' : 'play' ?>"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="post" class="d-inline" onsubmit="return confirm('Bu API key silinsin mi?')">
                                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                <input type="hidden" name="action" value="delete_key">
                                                <input type="hidden" name="key_id" value="<?= $key['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- API Key Ekleme Modal -->
    <div class="modal fade" id="addKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni API Key Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="add_key">
                        
                        <div class="mb-3">
                            <label for="key_name" class="form-label">Key Adı</label>
                            <input type="text" class="form-control" id="key_name" name="key_name" required
                                   placeholder="Örn: Ana API Key">
                        </div>
                        
                        <div class="mb-3">
                            <label for="api_key" class="form-label">Google Drive API Key</label>
                            <input type="text" class="form-control" id="api_key" name="api_key" required
                                   placeholder="AIza...">
                            <div class="form-text">
                                Google Cloud Console'dan aldığınız API key'i girin.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="daily_quota" class="form-label">Günlük Quota Limiti</label>
                            <input type="number" class="form-control" id="daily_quota" name="daily_quota" 
                                   value="100000000" min="1000">
                            <div class="form-text">
                                Günde maksimum kaç istek yapılabileceğini belirtin.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-primary">API Key Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>