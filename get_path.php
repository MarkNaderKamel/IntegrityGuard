<?php
/**
 * Path Finder - Shows exact paths for cron job setup
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Path Finder for Cron Job</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f4f4f4; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .path-box { background: #2d2d2d; color: #00ff00; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; margin: 15px 0; word-break: break-all; }
        .info { background: #d9edf7; border-left: 4px solid #5bc0de; padding: 15px; margin: 15px 0; }
        .warning { background: #fcf8e3; border-left: 4px solid #f0ad4e; padding: 15px; margin: 15px 0; }
        .success { background: #dff0d8; border-left: 4px solid #5cb85c; padding: 15px; margin: 15px 0; }
        h2 { color: #5bc0de; margin-top: 30px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Cron Job Path Finder</h1>
        
        <div class="info">
            <strong>Your Issue:</strong> "Could not open input file" means the cron job can't find monitor.php at the path you specified.
        </div>

        <h2>📁 Correct File Paths</h2>
        
        <p><strong>monitor.php location:</strong></p>
        <div class="path-box"><?php echo __DIR__ . '/monitor.php'; ?></div>
        
        <p><strong>PHP binary location (usually):</strong></p>
        <div class="path-box">/usr/bin/php</div>
        
        <p><strong>Alternative PHP paths to try:</strong></p>
        <div class="path-box">
            <?php echo PHP_BINARY; ?><br>
            /usr/local/bin/php<br>
            /opt/alt/php74/usr/bin/php<br>
            /opt/alt/php80/usr/bin/php
        </div>

        <h2>✅ Recommended Cron Commands</h2>
        
        <div class="success">
            <strong>Option 1 (Most Common):</strong>
            <div class="path-box">/usr/bin/php <?php echo __DIR__ . '/monitor.php'; ?></div>
        </div>

        <div class="success">
            <strong>Option 2 (Using PHP_BINARY from server):</strong>
            <div class="path-box"><?php echo PHP_BINARY; ?> <?php echo __DIR__ . '/monitor.php'; ?></div>
        </div>

        <div class="success">
            <strong>Option 3 (With cd command - if path issues persist):</strong>
            <div class="path-box">cd <?php echo __DIR__; ?> && /usr/bin/php monitor.php</div>
        </div>

        <h2>🔧 Troubleshooting Steps</h2>

        <div class="warning">
            <strong>Step 1: Verify File Exists</strong>
            <p>Current directory contents:</p>
            <ul>
                <?php
                $files = scandir(__DIR__);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        echo '<li>' . htmlspecialchars($file);
                        if ($file === 'monitor.php') {
                            echo ' <strong style="color: green;">✓ FOUND</strong>';
                        }
                        echo '</li>';
                    }
                }
                ?>
            </ul>
        </div>

        <div class="warning">
            <strong>Step 2: Check File Permissions</strong>
            <p>monitor.php permissions: 
            <?php
            if (file_exists(__DIR__ . '/monitor.php')) {
                $perms = substr(sprintf('%o', fileperms(__DIR__ . '/monitor.php')), -4);
                echo '<code>' . $perms . '</code>';
                if ($perms === '0644' || $perms === '0755') {
                    echo ' <span style="color: green;">✓ OK</span>';
                } else {
                    echo ' <span style="color: red;">⚠ May need to be 644 or 755</span>';
                }
            } else {
                echo '<span style="color: red;">FILE NOT FOUND!</span>';
            }
            ?>
            </p>
        </div>

        <h2>📋 Copy & Paste These Commands</h2>

        <p><strong>For cPanel Cron Job:</strong></p>
        <div class="info">
            <strong>Timing:</strong> <code>0 * * * *</code> (every hour)<br>
            <strong>Command:</strong> 
            <div class="path-box" style="margin-top: 10px;">/usr/bin/php <?php echo __DIR__ . '/monitor.php'; ?> >/dev/null 2>&1</div>
        </div>

        <h2>🐛 Common Issues & Solutions</h2>

        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr style="background: #f9f9f9;">
                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Error</th>
                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Solution</th>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><code>Could not open input file</code></td>
                <td style="padding: 10px; border: 1px solid #ddd;">Use the FULL path shown above</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><code>php: command not found</code></td>
                <td style="padding: 10px; border: 1px solid #ddd;">Use <code>/usr/bin/php</code> instead of <code>php</code></td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><code>Permission denied</code></td>
                <td style="padding: 10px; border: 1px solid #ddd;">Set file permissions to 644 or 755</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;">Wrong PHP version</td>
                <td style="padding: 10px; border: 1px solid #ddd;">Try <code>/opt/alt/php80/usr/bin/php</code></td>
            </tr>
        </table>

        <h2>🧪 Test Your Cron Command</h2>
        
        <div class="success">
            <strong>SSH Test (if you have SSH access):</strong>
            <p>Run this command directly to test:</p>
            <div class="path-box">/usr/bin/php <?php echo __DIR__ . '/monitor.php'; ?></div>
            <p>If it works via SSH, it will work in cron.</p>
        </div>

        <h2>📞 Hostinger-Specific Help</h2>
        
        <div class="info">
            <p><strong>For Hostinger users:</strong></p>
            <ol>
                <li>Login to hPanel</li>
                <li>Go to <strong>Advanced → Cron Jobs</strong></li>
                <li>Select <strong>Common Settings: Once Per Hour</strong></li>
                <li>Or use <strong>Custom</strong> and enter:</li>
                <ul>
                    <li><strong>Minute:</strong> 0</li>
                    <li><strong>Hour:</strong> *</li>
                    <li><strong>Day:</strong> *</li>
                    <li><strong>Month:</strong> *</li>
                    <li><strong>Weekday:</strong> *</li>
                </ul>
                <li><strong>Command:</strong> Copy from the box above</li>
                <li>Click <strong>Create</strong></li>
            </ol>
        </div>

        <div class="warning">
            <strong>⚠️ Important:</strong> Make sure to copy the EXACT path shown above. Even one wrong character will cause the "Could not open input file" error!
        </div>

        <p style="margin-top: 30px;"><a href="index.php">← Back to Dashboard</a></p>
    </div>
</body>
</html>
