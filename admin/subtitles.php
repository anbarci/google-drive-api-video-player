<?php
/**
 * Admin Panel - Subtitle Yönetimi
 */

require_once __DIR__ . '/../config.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = 'info';
$selectedVideoId = $_GET['video_id'] ?? '';

// Subtitle ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Güvenlik hatası!';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'];
        
        if ($action === 'add_subtitle') {
            $videoId = trim($_POST['video_id'] ?? '');
            $subtitleUrl = trim($_POST['subtitle_url'] ?? '');
            $languageCode = trim($_POST['language_code'] ?? '');
            $languageName = trim($_POST['language_name'] ?? '');
            $isDefault = isset($_POST['is_default']);
            
            if (empty($videoId) || empty($subtitleUrl) || empty($languageCode) || empty($languageName)) {
                $message = 'Tüm alanları doldurun!';
                $messageType = 'danger';
            } else {
                $subtitleId = extractDriveVideoId($subtitleUrl);
                
                if (!$subtitleId) {
                    $message = 'Geçersiz Google Drive subtitle URL!';
                    $messageType = 'danger';
                } else {
                    $subtitles = readJsonFile(SUBTITLES_FILE, []);
                    
                    // Aynı video için aynı dil var mı?
                    $exists = false;
                    foreach ($subtitles as $subtitle) {
                        if ($subtitle['video_id'] === $videoId && $subtitle['language_code'] === $languageCode) {
                            $exists = true;
                            break;
                        }
                    }
                    
                    if ($exists) {
                        $message = 'Bu video için bu dilde subtitle zaten var!';
                        $messageType = 'warning';
                    } else {
                        // Eğer default olarak işaretlendiyse, diğerlerini default'tan çıkar
                        if ($isDefault) {
                            foreach ($subtitles as &$subtitle) {
                                if ($subtitle['video_id'] === $videoId) {
                                    $subtitle['is_default'] = false;
                                }
                            }
                        }
                        
                        $newSubtitle = [
                            'id' => uniqid('sub_', true),
                            'video_id' => $videoId,
                            'subtitle_id' => $subtitleId,
                            'subtitle_url' => $subtitleUrl,
                            'language_code' => $languageCode,
                            'language_name' => $languageName,
                            'is_default' => $isDefault,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        
                        $subtitles[] = $newSubtitle;
                        
                        if (writeJsonFile(SUBTITLES_FILE, $subtitles)) {
                            $message = 'Subtitle başarıyla eklendi!';
                            $messageType = 'success';
                            
                            writeLog('INFO', 'Subtitle added', [
                                'video_id' => $videoId,
                                'language' => $languageCode
                            ], ADMIN_LOG_FILE);
                        } else {
                            $message = 'Subtitle kaydedilemedi!';
                            $messageType = 'danger';
                        }
                    }
                }
            }
        } elseif ($action === 'delete_subtitle') {
            $subtitleDbId = $_POST['subtitle_db_id'] ?? '';
            $subtitles = readJsonFile(SUBTITLES_FILE, []);
            
            $subtitles = array_filter($subtitles, function($subtitle) use ($subtitleDbId) {
                return $subtitle['id'] !== $subtitleDbId;
            });
            
            if (writeJsonFile(SUBTITLES_FILE, array_values($subtitles))) {
                $message = 'Subtitle silindi!';
                $messageType = 'success';
            }
        }
    }
}

$videos = readJsonFile(VIDEOS_FILE, []);
$subtitles = readJsonFile(SUBTITLES_FILE, []);

// Video filtreleme
if ($selectedVideoId) {
    $subtitles = array_filter($subtitles, function($subtitle) use ($selectedVideoId) {
        return $subtitle['video_id'] === $selectedVideoId;
    });
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subtitle Yönetimi - Admin Panel</title>
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
                    <h2><i class="fas fa-closed-captioning"></i> Subtitle Yönetimi</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubtitleModal">
                        <i class="fas fa-plus"></i> Yeni Subtitle
                    </button>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Video Filtresi -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label for="video_filter" class="form-label">Video Filtresi</label>
                                    <select class="form-select" id="video_filter" name="video_id">
                                        <option value="">Tüm videolar</option>
                                        <?php foreach ($videos as $video): ?>
                                        <option value="<?= htmlspecialchars($video['video_id']) ?>"
                                                <?= $selectedVideoId === $video['video_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($video['title']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-filter"></i> Filtrele
                                    </button>
                                    <?php if ($selectedVideoId): ?>
                                    <a href="subtitles.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Temizle
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($subtitles)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-closed-captioning fa-3x text-muted mb-3"></i>
                            <h5>Subtitle Bulunamadı</h5>
                            <p class="text-muted">
                                <?= $selectedVideoId ? 'Bu video için subtitle bulunamadı.' : 'Henüz hiç subtitle eklenmemiş.' ?>
                                Yeni subtitle eklemek için yukarıdaki butona tıklayın.
                            </p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Video</th>
                                        <th>Dil</th>
                                        <th>Subtitle ID</th>
                                        <th>Varsayılan</th>
                                        <th>Eklenme</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subtitles as $subtitle): ?>
                                    <?php
                                    // Video başlığını bul
                                    $videoTitle = 'Bilinmiyor';
                                    foreach ($videos as $video) {
                                        if ($video['video_id'] === $subtitle['video_id']) {
                                            $videoTitle = $video['title'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($videoTitle) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($subtitle['video_id']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= htmlspecialchars($subtitle['language_code']) ?></span>
                                            <br><?= htmlspecialchars($subtitle['language_name']) ?>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($subtitle['subtitle_id']) ?></code>
                                        </td>
                                        <td>
                                            <?php if ($subtitle['is_default']): ?>
                                                <span class="badge bg-success">Evet</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Hayır</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($subtitle['created_at'])) ?></td>
                                        <td>
                                            <a href="../api/subtitle.php?id=<?= urlencode($subtitle['subtitle_id']) ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-info" title="Önizle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <form method="post" class="d-inline" onsubmit="return confirm('Bu subtitle silinsin mi?')">
                                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                <input type="hidden" name="action" value="delete_subtitle">
                                                <input type="hidden" name="subtitle_db_id" value="<?= $subtitle['id'] ?>">
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
    
    <!-- Subtitle Ekleme Modal -->
    <div class="modal fade" id="addSubtitleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni Subtitle Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="add_subtitle">
                        
                        <div class="mb-3">
                            <label for="video_id" class="form-label">Video Seçin</label>
                            <select class="form-select" id="video_id" name="video_id" required>
                                <option value="">Video seçin...</option>
                                <?php foreach ($videos as $video): ?>
                                <option value="<?= htmlspecialchars($video['video_id']) ?>"
                                        <?= $selectedVideoId === $video['video_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($video['title']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subtitle_url" class="form-label">Google Drive Subtitle URL</label>
                            <input type="url" class="form-control" id="subtitle_url" name="subtitle_url" required
                                   placeholder="https://drive.google.com/file/d/SUBTITLE_ID/view">
                            <div class="form-text">
                                .srt, .vtt, .ass veya .ssa formatındaki subtitle dosyasının Google Drive linkini girin.
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="language_code" class="form-label">Dil Kodu</label>
                                <select class="form-select" id="language_code" name="language_code" required>
                                    <option value="">Dil seçin...</option>
                                    <?php foreach (SUBTITLE_SETTINGS['supported_languages'] as $code => $name): ?>
                                    <option value="<?= $code ?>"><?= $code ?> - <?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="language_name" class="form-label">Dil Adı</label>
                                <input type="text" class="form-control" id="language_name" name="language_name" required
                                       placeholder="Türkçe">
                            </div>
                        </div>
                        
                        <div class="mb-3 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                                <label class="form-check-label" for="is_default">
                                    Varsayılan subtitle olarak ayarla
                                </label>
                                <div class="form-text">
                                    Video oynatılırken otomatik olarak bu subtitle gösterilir.
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Subtitle Gereksinimleri:</h6>
                            <ul class="mb-0">
                                <li>Subtitle dosyasının Google Drive'da herkese açık olması gerekir</li>
                                <li>Desteklenen formatlar: .srt, .vtt, .ass, .ssa</li>
                                <li>Dosya boyutu: Maksimum 1MB</li>
                                <li>Karakter kodlaması: UTF-8 önerilir</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-primary">Subtitle Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dil kodu seçildiğinde dil adını otomatik doldur
        document.getElementById('language_code').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const languageName = selectedOption.text.split(' - ')[1];
                document.getElementById('language_name').value = languageName;
            }
        });
    </script>
</body>
</html>