# IntegrityGuard
# 🛡️ IntegrityGuard – File Integrity Monitoring System

IntegrityGuard is a lightweight and secure **File Integrity Monitoring (FIM)** system built in PHP.  
It monitors directories, detects file changes, and sends instant email alerts when unauthorized modifications are detected.

---

## 🚀 Features

- 🧠 Generates a **baseline** snapshot of all files (names, sizes, hashes).
- 🔍 Detects and reports:
  - New files added
  - Existing files modified
  - Files deleted
- 📧 Sends **email alerts** automatically when changes occur.
- ⚙️ Can be automated via **Cron Job** or manual execution.
- 🔒 Excludes specific files or folders from monitoring.
- 🌐 Simple web interface for configuration and testing.

---

## 🧩 File Structure

| File | Description |
|------|--------------|
| `index.php` | Entry page for the monitoring system; can trigger scans or show logs. |
| `monitor.php` | Core monitoring engine that scans directories and compares file hashes. |
| `baseline.php` | Generates or updates the baseline file containing original hashes. |
| `config.php` | Main configuration file (defines monitored path, exclusions, etc.). |
| `email_config.php` | Stores SMTP credentials and email recipient info. |
| `email_sender.php` | Handles the sending of email alerts using configured SMTP. |
| `get_path.php` | Retrieves or validates the directory to monitor. |
| `test_email.php` | Tests SMTP and email delivery configuration. |

---

## ⚙️ Requirements

- **PHP 7.4+** (recommended: PHP 8.0+)
- Enabled PHP functions:
  - `hash_file()`
  - `json_encode()`, `json_decode()`
  - `mail()` or PHPMailer SMTP support
- Proper file read permissions for monitored directories
- (Optional) Cron Job access for automation

---

## 🛠️ Installation & Setup

### 1. Upload Files
Upload the entire SentinelPHP folder (contents of `Monitor.zip`) to your web server.

### 2. Configure Directory
Open `config.php` and update settings:
```php
return [
    'rootDir' => '/path/to/monitor',   // Directory you want to monitor
    'excluded' => ['vendor', 'cache'], // Directories to ignore
];


3. Configure Email Alerts

Edit email_config.php:

return [
    'smtp_host' => 'smtp.example.com',
    'smtp_port' => 587,
    'smtp_user' => 'your@email.com',
    'smtp_pass' => 'yourpassword',
    'recipient' => 'admin@example.com',
];


4. Generate Baseline

Run baseline.php to create the initial snapshot of monitored files:

https://yourdomain.com/baseline.php

5. Run Monitoring Script

Manually check for file changes anytime:

https://yourdomain.com/monitor.php

6. Automate via Cron (Recommended)

Add this to your crontab to run every 6 hours:

0 */6 * * * /usr/bin/php /path/to/monitor/monitor.php


This ensures automated scanning and alerting of file changes.

🧪 Testing Email Functionality

Ensure SMTP settings in email_config.php are correct.

Visit:

https://yourdomain.com/test_email.php


You should receive a confirmation email verifying that alerts are working.

📬 Example Alert Email
Subject: File Change Detected - SentinelPHP Alert

Changes detected in monitored directory:
- Modified: /public_html/index.php
- Added: /public_html/new_file.php
- Deleted: /public_html/old_script.php

🧰 Troubleshooting

No Emails Sent:
Check SMTP credentials and port in email_config.php.
Use test_email.php to confirm email functionality.

Script Timeout:
Increase PHP execution limits (already set to unlimited in monitor.php):

set_time_limit(0);
ini_set('memory_limit', '256M');


Permission Errors:
Ensure PHP has read access to the target directory.
