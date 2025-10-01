<?php
/**
 * Subtitle API - Subtitle dosyasını serve eder
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DriveAPI.php';

$subtitleId = $_GET['id'] ?? '';
$format = $_GET['format'] ?? 'srt';

if (!$subtitleId || !validateVideoId($subtitleId)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid subtitle ID']);
    exit;
}

try {
    $driveAPI = new DriveAPI();
    $content = $driveAPI->getSubtitleContent($subtitleId);
    
    if (!$content) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Subtitle not found']);
        exit;
    }
    
    // Content-Type ayarla
    $contentType = 'text/plain';
    switch (strtolower($format)) {
        case 'vtt':
            $contentType = 'text/vtt';
            break;
        case 'srt':
            $contentType = 'text/srt';
            break;
        default:
            $contentType = 'text/plain';
    }
    
    header('Content-Type: ' . $contentType . '; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: public, max-age=3600');
    
    echo $content;
    
    writeLog('INFO', 'Subtitle served', ['subtitle_id' => $subtitleId], API_LOG_FILE);
    
} catch (Exception $e) {
    writeLog('ERROR', 'Subtitle serve failed', [
        'subtitle_id' => $subtitleId,
        'error' => $e->getMessage()
    ], ERROR_LOG_FILE);
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Internal server error']);
}
?>