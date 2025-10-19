<?php
/**
 * File Integrity Monitoring (FIM) System - Baseline Generator
 * 
 * Run this script manually to create the initial baseline of your files.
 * This should be done when your site is in a known good state.
 */

// Performance settings for large sites
set_time_limit(0); // Remove execution time limit
ini_set('memory_limit', '256M'); // Increase memory limit

// Load configuration
$config = require 'config.php';

/**
 * Recursively scan directory and calculate file hashes
 * Optimized for large file sets with progress reporting
 */
function scanDirectory($dir, $excluded, $rootDir, &$fileCount = 0) {
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
        
        // Check if item should be excluded
        if (in_array($item, $excluded)) {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        // Get relative path for storage
        $relativePath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $path);
        
        if (is_dir($path)) {
            // Recursively scan subdirectories
            $hashes = array_merge($hashes, scanDirectory($path, $excluded, $rootDir, $fileCount));
        } elseif (is_file($path)) {
            // Calculate SHA256 hash of the file
            $hash = hash_file('sha256', $path);
            if ($hash !== false) {
                $hashes[$relativePath] = $hash;
                $fileCount++;
                
                // Show progress every 100 files
                if ($fileCount % 100 == 0) {
                    echo "<script>document.getElementById('progress').innerHTML = 'Processing: " . $fileCount . " files scanned...';</script>";
                    flush();
                    ob_flush();
                }
            }
        }
    }
    
    return $hashes;
}

// Start scanning
echo "<!DOCTYPE html>\n<html>\n<head>\n<title>Baseline Generator</title>\n<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;}.success{color:green;}.info{color:blue;}.progress{color:#f0ad4e;font-weight:bold;}</style>\n</head>\n<body>\n";
echo "<h1>File Integrity Monitoring - Baseline Generator</h1>\n";
echo "<p class='info'>Scanning directory: " . htmlspecialchars($config['root_directory']) . "</p>\n";
echo "<p id='progress' class='progress'>Starting scan...</p>\n";

// Flush output to show progress immediately
flush();
ob_flush();

$fileCount = 0;
$baseline = scanDirectory($config['root_directory'], $config['excluded_files_and_dirs'], $config['root_directory'], $fileCount);

// Save baseline to JSON file
echo "<p class='info'>Saving baseline file...</p>\n";
flush();
ob_flush();

$jsonData = json_encode($baseline, JSON_PRETTY_PRINT);
if (file_put_contents($config['baseline_file_path'], $jsonData)) {
    echo "<script>document.getElementById('progress').style.display = 'none';</script>";
    echo "<p class='success'><strong>✓ Baseline created successfully!</strong></p>\n";
    echo "<p>Baseline saved to: <strong>" . htmlspecialchars($config['baseline_file_path']) . "</strong></p>\n";
    echo "<p>Total files indexed: <strong>" . $fileCount . "</strong></p>\n";
    echo "<p>Baseline file size: <strong>" . number_format(strlen($jsonData) / 1024, 2) . " KB</strong></p>\n";
    echo "<p><a href='index.php'>Go to Dashboard</a></p>\n";
} else {
    echo "<p style='color:red;'><strong>✗ Error:</strong> Could not write baseline file. Check file permissions.</p>\n";
}

echo "</body>\n</html>";
