<?php
/**
 * Admin Panel - Giriş Sayfası
 */

require_once __DIR__ . '/../config.php';

session_start();

// Zaten giriş yapmış mı?
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
$loginAttempts = $_SESSION['login_attempts'] ?? 0;
$lastAttempt = $_SESSION['last_attempt'] ?? 0;

// Giriş denemesi sınırı
if ($loginAttempts >= ADMIN_SETTINGS['max_login_attempts']) {
    $timeSinceLastAttempt = time() - $lastAttempt;
    if ($timeSinceLastAttempt < ADMIN_SETTINGS['lockout_duration']) {
        $remainingTime = ADMIN_SETTINGS['lockout_duration'] - $timeSinceLastAttempt;
        $error = "Çok fazla başarısız giriş denemesi. {$remainingTime} saniye sonra tekrar deneyin.";
    } else {
        // Kilidi kaldır
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_attempt']);
        $loginAttempts = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $loginAttempts < ADMIN_SETTINGS['max_login_attempts']) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!validateCsrfToken($csrfToken)) {
        $error = 'Güvenlik hatası. Sayfayı yenileyin.';
    } elseif ($username === ADMIN_SETTINGS['username'] && password_verify($password, ADMIN_SETTINGS['password'])) {
        // Başarılı giriş
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_login_time'] = time();
        
        // Giriş denemelerini sıfırla
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_attempt']);
        
        writeLog('INFO', 'Admin login successful', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ], ADMIN_LOG_FILE);
        
        header('Location: index.php');
        exit;
    } else {
        // Başarısız giriş
        $loginAttempts++;
        $_SESSION['login_attempts'] = $loginAttempts;
        $_SESSION['last_attempt'] = time();
        
        writeLog('WARNING', 'Admin login failed', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'attempts' => $loginAttempts
        ], ADMIN_LOG_FILE);
        
        $error = 'Kullanıcı adı veya şifre yanlış.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş - <?= PROJECT_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2>Admin Panel</h2>
            <p class="text-muted">Google Drive Video Player</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($loginAttempts < ADMIN_SETTINGS['max_login_attempts']): ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="fas fa-user"></i> Kullanıcı Adı
                </label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> Şifre
                </label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt"></i> Giriş Yap
            </button>
        </form>
        
        <div class="mt-3 text-center">
            <small class="text-muted">
                Kalan deneme: <?= ADMIN_SETTINGS['max_login_attempts'] - $loginAttempts ?>
            </small>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>