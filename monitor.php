<?php
/**
 * File Integrity Monitoring (FIM) System - Core Monitoring Script
 * 
 * This script should be executed by a Cron Job to check for file changes.
 * It compares the current file state with the baseline and sends email alerts.
 */

// Performance settings for large sites
set_time_limit(0); // Remove execution time limit
ini_set('memory_limit', '256M'); // Increase memory limit

// Load configuration
$config = require 'config.php';
require_once __DIR__ . '/email_sender.php';

/**
 * Recursively scan directory and calculate file hashes
 */
function scanDirectory($dir, $excluded, $rootDir) {
    $hashes = [];
    
    if (!is_dir($dir)) {
        return $hashes;
    }
    
    $items = @scandir($dir);
    
    if ($items === false) {
        return $hashes;
    }
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        if (in_array($item, $excluded)) {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $relativePath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $path);
        
        if (is_dir($path)) {
            $hashes = array_merge($hashes, scanDirectory($path, $excluded, $rootDir));
        } elseif (is_file($path)) {
            $hash = hash_file('sha256', $path);
            if ($hash !== false) {
                $hashes[$relativePath] = $hash;
            }
        }
    }
    
    return $hashes;
}

/**
 * Send email alert with detected changes
 */
function sendAlert($config, $modified, $added, $deleted) {
    $subject = "SECURITY ALERT: File Changes Detected on " . $config['site_name'] . "!";
    
    $currentDate = date('Y-m-d H:i:s');
    
    // Build HTML email body
    $message = "<!DOCTYPE html>\n<html>\n<head>\n<style>\n";
    $message .= "body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\n";
    $message .= "h1 { color: #d9534f; }\n";
    $message .= "h2 { color: #5bc0de; margin-top: 20px; }\n";
    $message .= "ul { background: #f4f4f4; padding: 15px 30px; border-left: 4px solid #d9534f; }\n";
    $message .= "li { margin: 5px 0; word-break: break-all; }\n";
    $message .= ".modified { border-left-color: #f0ad4e; }\n";
    $message .= ".added { border-left-color: #5bc0de; }\n";
    $message .= ".deleted { border-left-color: #d9534f; }\n";
    $message .= "</style>\n</head>\n<body>\n";
    
    $message .= "<h1>⚠️ Security Alert: File Changes Detected</h1>\n";
    $message .= "<p><strong>Website:</strong> " . htmlspecialchars($config['site_name']) . "</p>\n";
    $message .= "<p><strong>Detection Time:</strong> " . $currentDate . "</p>\n";
    $message .= "<hr>\n";
    
    if (!empty($modified)) {
        $message .= "<h2>📝 Modified Files (" . count($modified) . ")</h2>\n";
        $message .= "<ul class='modified'>\n";
        foreach ($modified as $file) {
            $message .= "<li>" . htmlspecialchars($file) . "</li>\n";
        }
        $message .= "</ul>\n";
    }
    
    if (!empty($added)) {
        $message .= "<h2>➕ Added Files (" . count($added) . ")</h2>\n";
        $message .= "<ul class='added'>\n";
        foreach ($added as $file) {
            $message .= "<li>" . htmlspecialchars($file) . "</li>\n";
        }
        $message .= "</ul>\n";
    }
    
    if (!empty($deleted)) {
        $message .= "<h2>🗑️ Deleted Files (" . count($deleted) . ")</h2>\n";
        $message .= "<ul class='deleted'>\n";
        foreach ($deleted as $file) {
            $message .= "<li>" . htmlspecialchars($file) . "</li>\n";
        }
        $message .= "</ul>\n";
    }
    
    $message .= "<hr>\n";
    $message .= "<p><small>This is an automated alert from your File Integrity Monitoring system.</small></p>\n";
    $message .= "</body>\n</html>";
    
    // Send email using the configured method
    return sendFIMEmail($config['email_to'], $config['email_from'], $subject, $message);
}

/**
 * Compare current state with baseline and detect changes
 */
function monitorFiles($returnOutput = false) {
    global $config;
    
    $output = [];
    
    // Check if baseline exists
    if (!file_exists($config['baseline_file_path'])) {
        $errorMsg = "Baseline file not found. Please run baseline.php first.";
        
        if ($returnOutput) {
            return ['error' => $errorMsg];
        }
        
        // Send alert email
        $subject = "ALERT: Baseline Missing - " . $config['site_name'];
        $message = "<p style='color:red;'><strong>Error:</strong> " . $errorMsg . "</p>";
        sendFIMEmail($config['email_to'], $config['email_from'], $subject, $message);
        
        exit(1);
    }
    
    // Load baseline
    $baseline = json_decode(file_get_contents($config['baseline_file_path']), true);
    
    // Scan current state
    $currentState = scanDirectory($config['root_directory'], $config['excluded_files_and_dirs'], $config['root_directory']);
    
    // Detect changes
    $modified = [];
    $added = [];
    $deleted = [];
    
    // Find modified and deleted files
    foreach ($baseline as $file => $hash) {
        if (!isset($currentState[$file])) {
            $deleted[] = $file;
        } elseif ($currentState[$file] !== $hash) {
            $modified[] = $file;
        }
    }
    
    // Find added files
    foreach ($currentState as $file => $hash) {
        if (!isset($baseline[$file])) {
            $added[] = $file;
        }
    }
    
    // If changes detected, send alert
    if (!empty($modified) || !empty($added) || !empty($deleted)) {
        if ($returnOutput) {
            return [
                'changes_detected' => true,
                'modified' => $modified,
                'added' => $added,
                'deleted' => $deleted
            ];
        }
        
        sendAlert($config, $modified, $added, $deleted);
    } else {
        if ($returnOutput) {
            return ['changes_detected' => false];
        }
    }
    
    return $returnOutput ? ['changes_detected' => false] : null;
}

// Run monitoring (only if not included by another script)
if (basename($_SERVER['PHP_SELF']) === 'monitor.php' && php_sapi_name() !== 'cli') {
    // Being accessed directly via web - show simple output
    echo "Monitoring check completed. See logs or email for details.";
} elseif (php_sapi_name() === 'cli' || !defined('DASHBOARD_MODE')) {
    // Running from CLI or cron - execute silently
    monitorFiles();
}
