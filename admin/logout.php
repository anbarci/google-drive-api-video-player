<?php
/**
 * Admin Panel - Çıkış
 */

require_once __DIR__ . '/../config.php';

session_start();

if (isset($_SESSION['admin_logged_in'])) {
    writeLog('INFO', 'Admin logout', [
        'username' => $_SESSION['admin_username'] ?? 'unknown',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ], ADMIN_LOG_FILE);
}

session_destroy();
header('Location: login.php?logout=1');
exit;
?>