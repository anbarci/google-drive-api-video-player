<?php
/**
 * Admin Panel - Video Yönetimi
 */

require_once __DIR__ . '/../config.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = 'info';

// Video ekleme/düzenleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Güvenlik hatası!';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'];
        
        if ($action === 'add_video') {
            $driveUrl = trim($_POST['drive_url'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($driveUrl) || empty($title)) {
                $message = 'Google Drive URL ve başlık gerekli!';
                $messageType = 'danger';
            } else {
                $videoId = extractDriveVideoId($driveUrl);
                
                if (!$videoId) {
                    $message = 'Geçersiz Google Drive URL!';
                    $messageType = 'danger';
                } else {
                    $videos = readJsonFile(VIDEOS_FILE, []);
                    
                    // Aynı video var mı kontrol et
                    $exists = false;
                    foreach ($videos as $video) {
                        if ($video['video_id'] === $videoId) {
                            $exists = true;
                            break;
                        }
                    }
                    
                    if ($exists) {
                        $message = 'Bu video zaten eklenmiş!';
                        $messageType = 'warning';
                    } else {
                        $newVideo = [
                            'id' => uniqid('video_', true),
                            'video_id' => $videoId,
                            'title' => $title,
                            'description' => $description,
                            'drive_url' => $driveUrl,
                            'created_at' => date('Y-m-d H:i:s'),
                            'views' => 0,
                            'status' => 'active'
                        ];
                        
                        $videos[] = $newVideo;
                        
                        if (writeJsonFile(VIDEOS_FILE, $videos)) {
                            $message = 'Video başarıyla eklendi!';
                            $messageType = 'success';
                            
                            writeLog('INFO', 'Video added', ['video_id' => $videoId, 'title' => $title], ADMIN_LOG_FILE);
                        } else {
                            $message = 'Video kaydedilemedi!';
                            $messageType = 'danger';
                        }
                    }
                }
            }
        } elseif ($action === 'delete_video') {
            $videoDbId = $_POST['video_db_id'] ?? '';
            $videos = readJsonFile(VIDEOS_FILE, []);
            
            $videos = array_filter($videos, function($video) use ($videoDbId) {
                return $video['id'] !== $videoDbId;
            });
            
            if (writeJsonFile(VIDEOS_FILE, array_values($videos))) {
                $message = 'Video silindi!';
                $messageType = 'success';
                
                writeLog('INFO', 'Video deleted', ['video_db_id' => $videoDbId], ADMIN_LOG_FILE);
            }
        }
    }
}

$videos = readJsonFile(VIDEOS_FILE, []);
// Son eklenen videolar üstte olsun
$videos = array_reverse($videos);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Yönetimi - Admin Panel</title>
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
                    <h2><i class="fas fa-video"></i> Video Yönetimi</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                        <i class="fas fa-plus"></i> Yeni Video
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
                        <?php if (empty($videos)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-video fa-3x text-muted mb-3"></i>
                            <h5>Video Bulunamadı</h5>
                            <p class="text-muted">Henüz hiç video eklenmemiş. İlk videonuzu eklemek için yukarıdaki butona tıklayın.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Başlık</th>
                                        <th>Video ID</th>
                                        <th>Eklenme</th>
                                        <th>İzlenme</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($videos as $video): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($video['title']) ?></strong>
                                            <?php if (!empty($video['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($video['description'], 0, 100)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($video['video_id']) ?></code>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($video['created_at'])) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= number_format($video['views']) ?></span>
                                        </td>
                                        <td>
                                            <a href="../player/embed.php?id=<?= urlencode($video['video_id']) ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-primary" title="Oynat">
                                                <i class="fas fa-play"></i>
                                            </a>
                                            
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="copyEmbedCode('<?= htmlspecialchars($video['video_id']) ?>')" title="Embed Kodu">
                                                <i class="fas fa-code"></i>
                                            </button>
                                            
                                            <a href="subtitles.php?video_id=<?= urlencode($video['video_id']) ?>" 
                                               class="btn btn-sm btn-outline-warning" title="Subtitle'lar">
                                                <i class="fas fa-closed-captioning"></i>
                                            </a>
                                            
                                            <form method="post" class="d-inline" onsubmit="return confirm('Bu video silinsin mi?')">
                                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                <input type="hidden" name="action" value="delete_video">
                                                <input type="hidden" name="video_db_id" value="<?= $video['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil">
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
    
    <!-- Video Ekleme Modal -->
    <div class="modal fade" id="addVideoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni Video Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="add_video">
                        
                        <div class="mb-3">
                            <label for="drive_url" class="form-label">Google Drive Video URL</label>
                            <input type="url" class="form-control" id="drive_url" name="drive_url" required
                                   placeholder="https://drive.google.com/file/d/VIDEO_ID/view">
                            <div class="form-text">
                                Google Drive'dan paylaşım linkini kopyalayıp buraya yapıştırın.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Video Başlığı</label>
                            <input type="text" class="form-control" id="title" name="title" required
                                   placeholder="Video başlığını girin">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama (Opsiyonel)</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Video hakkında kısa açıklama..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Önemli Bilgiler:</h6>
                            <ul class="mb-0">
                                <li>Video dosyasının Google Drive'da "Bağlantıyı bilen herkes görüntüleyebilir" olarak ayarlanmış olması gerekir.</li>
                                <li>Desteklenen formatlar: MP4, AVI, MOV, WMV, FLV, WebM</li>
                                <li>Maksimum dosya boyutu: Google Drive limitine bağlı</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-primary">Video Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyEmbedCode(videoId) {
            const embedCode = `<iframe src="${window.location.origin}/../player/embed.php?id=${videoId}" width="800" height="450" frameborder="0" allowfullscreen></iframe>`;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(embedCode).then(() => {
                    alert('Embed kodu kopyalandı!');
                });
            } else {
                prompt('Embed kodunu kopyalayın:', embedCode);
            }
        }
    </script>
</body>
</html>