<?php
/**
 * File Integrity Monitoring (FIM) System - Control Panel Dashboard
 * * Password-protected dashboard for managing the monitoring system.
 */

session_start();

// Load configuration
$config = require 'config.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle login
if (isset($_POST['password'])) {
    if ($_POST['password'] === $config['dashboard_password']) {
        $_SESSION['authenticated'] = true;
        header('Location: index.php');
        exit;
    } else {
        $loginError = "Invalid password!";
    }
}

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>FIM Dashboard - Login</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
            h2 { margin-top: 0; color: #333; }
            input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            button { width: 100%; padding: 12px; background: #5cb85c; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
            button:hover { background: #4cae4c; }
            .error { color: #d9534f; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>🔒 FIM Dashboard</h2>
            <form method="POST">
                <input type="password" name="password" placeholder="Enter password" required autofocus>
                <button type="submit">Login</button>
            </form>
            <?php if (isset($loginError)): ?>
                <p class="error"><?php echo htmlspecialchars($loginError); ?></p>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle actions
define('DASHBOARD_MODE', true);

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'regenerate_baseline') {
        require 'baseline.php';
        exit;
    } elseif ($_POST['action'] === 'manual_check') {
        require 'monitor.php';
        $result = monitorFiles(true);
    }
}

// Get last scan time
$lastScan = 'Never';
if (file_exists($config['baseline_file_path'])) {
    $lastScan = date('Y-m-d H:i:s', filemtime($config['baseline_file_path']));
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>FIM Dashboard - Control Panel</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        h1 { color: #333; margin-bottom: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #5cb85c; padding-bottom: 15px; margin-bottom: 30px; }
        .logout { background: #d9534f; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .logout:hover { background: #c9302c; }
        .info-box { background: #f9f9f9; border-left: 4px solid #5bc0de; padding: 15px; margin-bottom: 20px; }
        .info-box strong { color: #5bc0de; }
        .button-group { display: flex; gap: 15px; margin: 20px 0; flex-wrap: wrap; }
        button { padding: 15px 30px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: #5cb85c; color: white; }
        .btn-primary:hover { background: #4cae4c; }
        .btn-warning { background: #f0ad4e; color: white; }
        .btn-warning:hover { background: #ec971f; }
        .result-box { margin-top: 20px; padding: 20px; border-radius: 5px; }
        .success { background: #dff0d8; border: 1px solid #d6e9c6; color: #3c763d; }
        .warning { background: #fcf8e3; border: 1px solid #faebcc; color: #8a6d3b; }
        .danger { background: #f2dede; border: 1px solid #ebccd1; color: #a94442; }
        ul { margin-left: 20px; margin-top: 10px; }
        li { margin: 5px 0; word-break: break-all; }
        h3 { margin: 15px 0 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ File Integrity Monitoring Dashboard</h1>
            <a href="?logout=1" class="logout">Logout</a>
        </div>
        
        <div class="info-box">
            <p><strong>Website:</strong> <?php echo htmlspecialchars($config['site_name']); ?></p>
            <p><strong>Root Directory:</strong> <?php echo htmlspecialchars($config['root_directory']); ?></p>
            <p><strong>Alert Email(s):</strong> <?php 
                if (is_array($config['email_to'])) {
                    echo htmlspecialchars(implode(', ', $config['email_to']));
                } else {
                    echo htmlspecialchars($config['email_to']);
                }
            ?></p>
            <p><strong>Last Baseline Update:</strong> <?php echo htmlspecialchars($lastScan); ?></p>
        </div>
        
        <div class="button-group">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="manual_check">
                <button type="submit" class="btn-primary">🔍 Run Manual Check Now</button>
            </form>
            
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="regenerate_baseline">
                <button type="submit" class="btn-warning" onclick="return confirm('This will regenerate the baseline and approve all current files. Continue?');">🔄 Re-generate File Baseline</button>
            </form>
        </div>
        
        <?php if (isset($result)): ?>
            <?php if (isset($result['error'])): ?>
                <div class="result-box danger">
                    <strong>Error:</strong> <?php echo htmlspecialchars($result['error']); ?>
                </div>
            <?php elseif ($result['changes_detected']): ?>
                <div class="result-box warning">
                    <h2>⚠️ Changes Detected!</h2>
                    
                    <?php if (!empty($result['modified'])): ?>
                        <h3>📝 Modified Files (<?php echo count($result['modified']); ?>):</h3>
                        <ul>
                            <?php foreach ($result['modified'] as $file): ?>
                                <li><?php echo htmlspecialchars($file); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    
                    <?php if (!empty($result['added'])): ?>
                        <h3>➕ Added Files (<?php echo count($result['added']); ?>):</h3>
                        <ul>
                            <?php foreach ($result['added'] as $file): ?>
                                <li><?php echo htmlspecialchars($file); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    
                    <?php if (!empty($result['deleted'])): ?>
                        <h3>🗑️ Deleted Files (<?php echo count($result['deleted']); ?>):</h3>
                        <ul>
                            <?php foreach ($result['deleted'] as $file): ?>
                                <li><?php echo htmlspecialchars($file); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="result-box success">
                    <strong>✓ All Clear!</strong> No file changes detected. Your website files match the baseline.
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 14px;">
            <h3>📋 Setup Instructions:</h3>
            <ol style="margin-left: 20px; line-height: 1.8;">
                <li>Edit <code>config.php</code> to set your email address and password</li>
                <li>Visit <code>baseline.php</code> once to create the initial baseline</li>
                <li>Set up a cron job to run <code>monitor.php</code> (e.g., every hour)</li>
                <li>Use this dashboard to manually check or update the baseline</li>
            </ol>
            <p style="margin-top: 15px;"><strong>Cron Job Example:</strong> <code>0 * * * * /usr/bin/php /path/to/monitor.php</code></p>
        </div>
        
        <footer style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #888;">
            <p>Made By Allam 🕵</p>
        </footer>
    </div>
</body>
</html>
