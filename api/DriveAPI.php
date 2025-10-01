<?php
/**
 * Google Drive API Manager
 * 
 * Çoklu API key desteği ve otomatik rotasyon sistemi
 * 
 * @author anbarci
 * @version 2.0.0
 */

require_once __DIR__ . '/../config.php';

class DriveAPI {
    private $apiKeys = [];
    private $currentKeyIndex = 0;
    private $keyStats = [];
    
    public function __construct() {
        $this->loadApiKeys();
        $this->loadKeyStats();
    }
    
    /**
     * API key'leri yükle
     */
    private function loadApiKeys() {
        $keysData = readJsonFile(APIKEYS_FILE, []);
        
        foreach ($keysData as $keyData) {
            if ($keyData['enabled'] && !$keyData['blocked']) {
                $this->apiKeys[] = [
                    'id' => $keyData['id'],
                    'key' => decryptApiKey($keyData['encrypted_key']),
                    'name' => $keyData['name'],
                    'daily_quota' => $keyData['daily_quota'] ?? 100000000,
                    'requests_today' => $keyData['requests_today'] ?? 0,
                    'last_used' => $keyData['last_used'] ?? null,
                    'error_count' => $keyData['error_count'] ?? 0,
                    'blocked_until' => $keyData['blocked_until'] ?? null
                ];
            }
        }
        
        if (empty($this->apiKeys)) {
            throw new Exception('No active API keys available');
        }
    }
    
    /**
     * Video bilgilerini al
     */
    public function getVideoInfo($videoId) {
        if (!validateVideoId($videoId)) {
            throw new Exception('Invalid video ID format');
        }
        
        $maxRetries = count($this->apiKeys);
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            try {
                $apiKey = $this->getCurrentApiKey();
                
                $url = str_replace('{FILE_ID}', $videoId, DRIVE_API_SETTINGS['info_url']);
                $url .= '?key=' . $apiKey['key'] . '&fields=id,name,mimeType,size,videoMediaMetadata,thumbnailLink,webViewLink';
                
                $response = $this->makeRequest($url);
                
                if ($response !== false) {
                    $videoInfo = json_decode($response, true);
                    
                    if (isset($videoInfo['error'])) {
                        $this->handleApiError($videoInfo['error']);
                        $attempt++;
                        continue;
                    }
                    
                    $this->updateKeyUsage($apiKey['id'], true);
                    return $videoInfo;
                }
                
                $attempt++;
                
            } catch (Exception $e) {
                writeLog('ERROR', 'Video info request failed', [
                    'video_id' => $videoId,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage()
                ]);
                
                $this->rotateApiKey();
                $attempt++;
            }
        }
        
        throw new Exception('Failed to get video info after all retries');
    }
    
    /**
     * Video stream URL'ini al
     */
    public function getVideoStreamUrl($videoId) {
        if (!validateVideoId($videoId)) {
            throw new Exception('Invalid video ID format');
        }
        
        $apiKey = $this->getCurrentApiKey();
        $url = str_replace('{FILE_ID}', $videoId, DRIVE_API_SETTINGS['download_url']);
        $url .= '&key=' . $apiKey['key'];
        
        $this->updateKeyUsage($apiKey['id'], true);
        return $url;
    }
    
    private function getCurrentApiKey() {
        return $this->apiKeys[$this->currentKeyIndex];
    }
    
    private function rotateApiKey() {
        $this->currentKeyIndex = ($this->currentKeyIndex + 1) % count($this->apiKeys);
        writeLog('INFO', 'API key rotated', ['new_index' => $this->currentKeyIndex]);
    }
    
    private function makeRequest($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Drive Video Player'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('HTTP error: ' . $httpCode);
        }
        
        return $response;
    }
    
    private function handleApiError($error) {
        writeLog('WARNING', 'API error', $error);
        $this->rotateApiKey();
    }
    
    private function updateKeyUsage($keyId, $success) {
        // API key kullanım istatistiklerini güncelle
    }
    
    private function loadKeyStats() {
        $this->keyStats = readJsonFile(STATS_FILE, []);
    }
}
?>