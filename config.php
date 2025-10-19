<?php
/**
 * File Integrity Monitoring (FIM) System - Configuration File
 * 
 * This file contains all user-configurable variables for the monitoring system.
 * Edit these values according to your needs before running the system.
 */

return [
    // Email address(es) where alerts will be sent
    // Single email: 'your-email@example.com'
    // Multiple emails: ['email1@example.com', 'email2@example.com', 'email3@example.com']
    'email_to' => ['email1@example.com', 'email2@example.com', 'email3@example.com'],
    
    // "From" email address for alerts
    'email_from' => 'your-email@example.com',
    
    // Website name (used in email subject)
    'site_name' => 'Website name',
    
    // Root directory to scan (parent directory - goes up one level from system folder)
    'root_directory' => dirname(__DIR__),
    
    // Files and directories to exclude from scanning
    'excluded_files_and_dirs' => [
        '.git',
        'cache',
        'logs',
        'baseline.json',
        'config.php',
        'baseline.php',
        'monitor.php',
        'index.php',
        'email_config.php',
        'email_sender.php',
        'test_email.php',
        '.htaccess',
        'error_log',
        'temp',
        'tmp',
        'sessions',
        'system'  // Exclude the monitoring system folder itself
    ],
    
    // Path to the baseline file
    'baseline_file_path' => __DIR__ . '/baseline.json',
    
    // Dashboard password (change this for security!)
    'dashboard_password' => 'password'
];
