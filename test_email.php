<?php
/**
 * Email Testing Script
 * Run this to test if email configuration is working
 */

// Performance settings
set_time_limit(30);

// Load configurations
$config = require 'config.php';
$emailConfig = require 'email_config.php';
require 'email_sender.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Test - FIM System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f4f4f4; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .info { background: #d9edf7; border-left: 4px solid #5bc0de; padding: 15px; margin: 15px 0; }
        .success { background: #dff0d8; border-left: 4px solid #5cb85c; padding: 15px; margin: 15px 0; color: #3c763d; }
        .error { background: #f2dede; border-left: 4px solid #d9534f; padding: 15px; margin: 15px 0; color: #a94442; }
        button { background: #5cb85c; color: white; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #4cae4c; }
        .config-info { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Configuration Test</h1>
        
        <div class="info">
            <strong>Current Email Method:</strong> <?php echo htmlspecialchars($emailConfig['email_method']); ?><br>
            <strong>Sending To:</strong> <?php 
                if (is_array($config['email_to'])) {
                    echo htmlspecialchars(implode(', ', $config['email_to']));
                } else {
                    echo htmlspecialchars($config['email_to']);
                }
            ?><br>
            <strong>Sending From:</strong> <?php echo htmlspecialchars($config['email_from']); ?>
            
            <?php if ($emailConfig['email_method'] === 'smtp'): ?>
                <br><strong>SMTP Host:</strong> <?php echo htmlspecialchars($emailConfig['smtp']['host']); ?>
                <br><strong>SMTP Port:</strong> <?php echo htmlspecialchars($emailConfig['smtp']['port']); ?>
            <?php endif; ?>
        </div>

        <?php if (isset($_POST['send_test'])): ?>
            <?php
            $subject = "Test Email - FIM System";
            $message = "<h2>Email Test Successful!</h2>";
            $message .= "<p>This is a test email from your File Integrity Monitoring system.</p>";
            $message .= "<p><strong>Sent at:</strong> " . date('Y-m-d H:i:s') . "</p>";
            $message .= "<p>If you received this email, your email configuration is working correctly!</p>";
            
            $errorDetails = [];
            $result = sendFIMEmail($config['email_to'], $config['email_from'], $subject, $message, $errorDetails);
            
            if ($result): ?>
                <div class="success">
                    <strong>✓ Email Sent Successfully!</strong><br>
                    Check your inbox at: <?php 
                        if (is_array($config['email_to'])) {
                            echo htmlspecialchars(implode(', ', $config['email_to']));
                        } else {
                            echo htmlspecialchars($config['email_to']);
                        }
                    ?><br>
                    <small>Note: It may take a few minutes to arrive, and check your spam folder.</small>
                    
                    <?php if (!empty($errorDetails)): ?>
                        <br><br><strong>Connection Details:</strong>
                        <ul style="margin: 5px 0; padding-left: 20px;">
                        <?php foreach ($errorDetails as $detail): ?>
                            <li><?php echo htmlspecialchars($detail); ?></li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>✗ Email Failed to Send</strong><br>
                    
                    <?php if (!empty($errorDetails)): ?>
                        <br><strong>Error Details:</strong>
                        <ul style="margin: 5px 0; padding-left: 20px;">
                        <?php foreach ($errorDetails as $detail): ?>
                            <li><?php echo htmlspecialchars($detail); ?></li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    
                    <br><strong>Troubleshooting Steps:</strong>
                    <ol>
                        <li>If using 'php_mail': Your server may not support mail(). Try SMTP method instead.</li>
                        <li>If using 'smtp': Check your SMTP credentials in email_config.php</li>
                        <li>For Gmail: Use an "App Password" not your regular password</li>
                        <li>Check server error logs for more details</li>
                    </ol>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST">
            <button type="submit" name="send_test">Send Test Email</button>
        </form>

        <h2>Configuration Guide</h2>
        
        <h3>Method 1: PHP mail() (Simplest, may not work)</h3>
        <div class="config-info">
// In email_config.php, set:<br>
'email_method' => 'php_mail'
        </div>

        <h3>Method 2: SMTP with Gmail (Recommended)</h3>
        <div class="config-info">
// In email_config.php, set:<br>
'email_method' => 'smtp',<br>
'smtp' => [<br>
&nbsp;&nbsp;'host' => 'smtp.gmail.com',<br>
&nbsp;&nbsp;'port' => 587,<br>
&nbsp;&nbsp;'username' => 'your-email@gmail.com',<br>
&nbsp;&nbsp;'password' => 'your-app-password', // See below<br>
&nbsp;&nbsp;'encryption' => 'tls',<br>
]
        </div>
        <p><strong>Gmail App Password Setup:</strong></p>
        <ol>
            <li>Go to Google Account → Security</li>
            <li>Enable 2-Step Verification</li>
            <li>Search for "App Passwords"</li>
            <li>Generate a new app password for "Mail"</li>
            <li>Use that 16-character password (not your Gmail password)</li>
        </ol>

        <h3>Method 3: SMTP with Hostinger</h3>
        <div class="config-info">
// In email_config.php, set:<br>
'email_method' => 'smtp',<br>
'smtp' => [<br>
&nbsp;&nbsp;'host' => 'smtp.hostinger.com',<br>
&nbsp;&nbsp;'port' => 587,<br>
&nbsp;&nbsp;'username' => 'your-email@yourdomain.com',<br>
&nbsp;&nbsp;'password' => 'your-email-password',<br>
&nbsp;&nbsp;'encryption' => 'tls',<br>
]
        </div>

        <p><a href="index.php">← Back to Dashboard</a></p>
    </div>
</body>
</html>
