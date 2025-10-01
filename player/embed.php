<?php
/**
 * Embed Video Player - Temiz player (başlık/bilgi gizli)
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/DriveAPI.php';

$videoId = $_GET['id'] ?? '';
$playerType = $_GET['player'] ?? 'plyr';

if (!$videoId || !validateVideoId($videoId)) {
    http_response_code(400);
    die('Invalid video ID');
}

try {
    $driveAPI = new DriveAPI();
    $videoInfo = $driveAPI->getVideoInfo($videoId);
    $streamUrl = $driveAPI->getVideoStreamUrl($videoId);
} catch (Exception $e) {
    writeLog('ERROR', 'Failed to load video', ['video_id' => $videoId, 'error' => $e->getMessage()]);
    http_response_code(500);
    die('Video could not be loaded');
}

// Subtitle kontrolü
$subtitles = [];
$subtitleData = readJsonFile(SUBTITLES_FILE, []);
foreach ($subtitleData as $subtitle) {
    if ($subtitle['video_id'] === $videoId) {
        $subtitles[] = $subtitle;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Player</title>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            background: #000;
            font-family: Arial, sans-serif;
        }
        
        .player-container {
            width: 100%;
            height: 100vh;
            position: relative;
            background: #000;
        }
        
        .plyr {
            width: 100%;
            height: 100%;
        }
        
        video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="player-container">
        <video id="player" controls crossorigin playsinline>
            <source src="<?= htmlspecialchars($streamUrl) ?>" type="video/mp4">
            
            <?php foreach ($subtitles as $subtitle): ?>
            <track kind="subtitles" 
                   label="<?= htmlspecialchars($subtitle['language_name']) ?>" 
                   src="../api/subtitle.php?id=<?= urlencode($subtitle['subtitle_id']) ?>" 
                   srclang="<?= htmlspecialchars($subtitle['language_code']) ?>"
                   <?= $subtitle['is_default'] ? 'default' : '' ?>>
            <?php endforeach; ?>
        </video>
    </div>
    
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script>
        const player = new Plyr('#player', {
            controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'captions', 'settings', 'fullscreen'],
            settings: ['captions', 'quality', 'speed'],
            captions: { active: true, language: 'auto' },
            fullscreen: { enabled: true, fallback: true, iosNative: true }
        });
        
        // Video yüklendiğinde otomatik başlat
        player.on('ready', () => {
            console.log('Player ready');
        });
        
        // Hata yönetimi
        player.on('error', (event) => {
            console.error('Player error:', event);
        });
    </script>
</body>
</html>