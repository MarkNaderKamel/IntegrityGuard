# 🛡️ IntegrityGuard  
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue?logo=php)
![License](https://img.shields.io/badge/License-MIT-green)
![Status](https://img.shields.io/badge/Status-Stable-brightgreen)
![Security](https://img.shields.io/badge/Security-Checked-success)

### Lightweight PHP-Based File Integrity Monitoring (FIM) System

**IntegrityGuard** is a secure, self-hosted, and lightweight **File Integrity Monitoring (FIM)** system built in PHP.  
It continuously monitors directories, detects unauthorized file changes, and instantly sends **email alerts** when suspicious modifications occur.

---

## 🚀 Features

- 🧠 **Baseline Generation** — Create secure snapshots of all files (name, size, hash).  
- 🔍 **Change Detection** — Detects:
  - 🆕 New files added  
  - ✏️ Existing files modified  
  - ❌ Files deleted  
- 📧 **Email Notifications** — Sends automatic alerts when file changes are detected.  
- ⚙️ **Automation Ready** — Can be triggered manually or via a Cron Job.  
- 🔒 **Smart Exclusion Rules** — Exclude specific directories or files from scans.  
- 🌐 **Simple Web Interface** — Intuitive interface for configuration, scanning, and testing.

---

## 📁 File Structure

| File | Description |
|------|--------------|
| **index.php** | Entry dashboard for the monitoring system (manual scan or logs view). |
| **monitor.php** | Core engine that scans directories and compares file hashes. |
| **baseline.php** | Generates or updates the baseline snapshot of all files. |
| **config.php** | Defines monitored paths, exclusions, and general configuration. |
| **email_config.php** | Stores SMTP credentials and recipient information. |
| **email_sender.php** | Handles PHPMailer or mail() alerts delivery. |
| **get_path.php** | Retrieves and validates target directory paths. |
| **test_email.php** | Tests SMTP configuration and verifies email delivery. |

---

## ⚙️ Requirements

- **PHP 7.4+** (Recommended: PHP 8.0+)
- Enabled PHP functions:
  - `hash_file()`
  - `json_encode()`, `json_decode()`
  - `mail()` or PHPMailer (for SMTP)
- Proper file read permissions on monitored directories  
- *(Optional)* Cron access for automation

---

## 🛠️ Installation & Setup

### 1. 📦 Upload Files
Upload the entire **IntegrityGuard** folder to your web server.

---

### 2. ⚙️ Configure Directory

Edit `config.php` to define what to monitor:

```php
return [
    'rootDir' => '/path/to/monitor',   // Directory to monitor
    'excluded' => ['vendor', 'cache'], // Directories to ignore
];
```

---

### 3. ✉️ Configure Email Alerts

Edit `email_config.php` with your SMTP settings:

```php
return [
    'smtp_host' => 'smtp.example.com',
    'smtp_port' => 587,
    'smtp_user' => 'your@email.com',
    'smtp_pass' => 'yourpassword',
    'recipient' => 'admin@example.com',
];
```

---

### 4. 🧾 Generate Baseline

Create the initial file snapshot by visiting:

```
https://yourdomain.com/baseline.php
```

---

### 5. 🔎 Run Monitoring Script

Manually check for file changes at any time:

```
https://yourdomain.com/monitor.php
```

---

### 6. ⏰ Automate with Cron (Recommended)

Add the following line to your **crontab** to run every 6 hours:

```
0 */6 * * * /usr/bin/php /path/to/monitor/monitor.php
```

This ensures continuous scanning and automatic alerts for any unauthorized file changes.

---

## 🧪 Testing Email Functionality

To confirm SMTP configuration works correctly, visit:

```
https://yourdomain.com/test_email.php
```

You should receive a test confirmation email.

---

## 📬 Example Alert Email

**Subject:** `File Change Detected - IntegrityGuard Alert`

**Message:**
```
Changes detected in monitored directory:
- Modified: /public_html/index.php
- Added: /public_html/new_file.php
- Deleted: /public_html/old_script.php
```

---

## 🧰 Troubleshooting

### ❌ No Emails Sent
- Verify SMTP credentials and port in `email_config.php`.  
- Use `test_email.php` to confirm mail functionality.

### 🕒 Script Timeout
Increase PHP execution limits (already configured in `monitor.php`):

```php
set_time_limit(0);
ini_set('memory_limit', '256M');
```

### 🔐 Permission Errors
Ensure PHP has **read access** to the target directory.

---

## 🧾 License

This project is licensed under the **MIT License**.  
You are free to use, modify, and distribute with proper attribution.

---

## 💡 Contributing

Pull requests are welcome!  
For major updates or enhancements, open an issue to discuss proposed changes first.

---

## ⭐ Support the Project

If you find **IntegrityGuard** useful, please give it a ⭐ on GitHub — it helps others discover the project and supports further development!

---

> **IntegrityGuard** — Because Security Should Be Simple 🛡️  
> _Developed By Mark in PHP_
