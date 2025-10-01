<?php
/**
 * Admin Panel - Ana Sayfa
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/DriveAPI.php';

session_start();

// Giriş kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Session timeout kontrolü
if (time() - $_SESSION['admin_login_time'] > ADMIN_SETTINGS['session_timeout']) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// İstatistikleri al
try {
    $driveAPI = new DriveAPI();
    $apiStats = $driveAPI->getStats();
    $keyStatus = $driveAPI->getApiKeyStatus();
} catch (Exception $e) {
    $apiStats = [];
    $keyStatus = [];
}

$videos = readJsonFile(VIDEOS_FILE, []);
$apiKeys = readJsonFile(APIKEYS_FILE, []);
$subtitles = readJsonFile(SUBTITLES_FILE, []);
$generalStats = readJsonFile(STATS_FILE, []);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= PROJECT_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        }
        .content {
            min-height: 100vh;
            background: #f8f9fa;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar px-0">
                <div class="p-3">
                    <h4 class="text-white">
                        <i class="fas fa-video"></i> Admin Panel
                    </h4>
                    <hr class="text-white">
                    
                    <nav class="nav flex-column">
                        <a class="nav-link text-white active" href="index.php">
                            <i class="fas fa-dashboard"></i> Dashboard
                        </a>
                        <a class="nav-link text-white" href="videos.php">
                            <i class="fas fa-video"></i> Video Yönetimi
                        </a>
                        <a class="nav-link text-white" href="apikeys.php">
                            <i class="fas fa-key"></i> API Key'ler
                        </a>
                        <a class="nav-link text-white" href="subtitles.php">
                            <i class="fas fa-closed-captioning"></i> Subtitle'lar
                        </a>
                        <hr class="text-white">
                        <a class="nav-link text-white" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Çıkış
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- İçerik -->
            <div class="col-md-9 col-lg-10 content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>Dashboard</h1>
                        <div class="text-muted">
                            Hoş geldin, <?= htmlspecialchars($_SESSION['admin_username']) ?>
                        </div>
                    </div>
                    
                    <!-- İstatistik Kartları -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card text-center">
                                <h3 class="text-primary"><?= count($videos) ?></h3>
                                <p class="mb-0">Toplam Video</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card text-center">
                                <h3 class="text-success"><?= count($apiKeys) ?></h3>
                                <p class="mb-0">API Key</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card text-center">
                                <h3 class="text-warning"><?= count($subtitles) ?></h3>
                                <p class="mb-0">Subtitle</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card text-center">
                                <h3 class="text-info"><?= $generalStats['total_views'] ?? 0 ?></h3>
                                <p class="mb-0">Toplam İzlenme</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- API Key Durumu -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-key"></i> API Key Durumu</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($keyStatus)): ?>
                                    <p class="text-warning">API key bulunamadı.</p>
                                    <?php else: ?>
                                    <?php foreach ($keyStatus as $key): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>
                                            <?= htmlspecialchars($key['name']) ?>
                                            <?php if ($key['is_current']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="text-muted">
                                            <?= $key['quota_percentage'] ?>% kullanıldı
                                        </span>
                                    </div>
                                    <div class="progress mb-3" style="height: 5px;">
                                        <div class="progress-bar" style="width: <?= $key['quota_percentage'] ?>%"></div>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-line"></i> API İstatistikleri</h5>
                                </div>
                                <div class="card-body">
                                    <p>Toplam İstek: <strong><?= $apiStats['total_requests'] ?? 0 ?></strong></p>
                                    <p>Başarılı: <strong><?= $apiStats['successful_requests'] ?? 0 ?></strong></p>
                                    <p>Başarısız: <strong><?= $apiStats['failed_requests'] ?? 0 ?></strong></p>
                                    <p>Key Rotasyonu: <strong><?= $apiStats['key_rotations'] ?? 0 ?></strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Son Videolar -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5><i class="fas fa-video"></i> Son Eklenen Videolar</h5>
                                    <a href="videos.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Yeni Video
                                    </a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($videos)): ?>
                                    <p class="text-muted">Henüz video eklenmemiş.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Başlık</th>
                                                    <th>Video ID</th>
                                                    <th>Eklenme Tarihi</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice(array_reverse($videos), 0, 5) as $video): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($video['title']) ?></td>
                                                    <td><code><?= htmlspecialchars($video['video_id']) ?></code></td>
                                                    <td><?= date('d.m.Y H:i', strtotime($video['created_at'])) ?></td>
                                                    <td>
                                                        <a href="../player/embed.php?id=<?= urlencode($video['video_id']) ?>" 
                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-play"></i>
                                                        </a>
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
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>