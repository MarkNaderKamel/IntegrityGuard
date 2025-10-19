<?php
/**
 * Email Sender Functions
 * Supports both PHP mail() and SMTP
 */

/**
 * Send email using PHP's built-in mail() function
 */
function sendEmailPHPMail($to, $from, $subject, $htmlMessage) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Support multiple recipients
    if (is_array($to)) {
        $to = implode(', ', $to);
    }
    
    return mail($to, $subject, $htmlMessage, $headers);
}

/**
 * Send email using SMTP (more reliable)
 */
function sendEmailSMTP($to, $from, $subject, $htmlMessage, $smtpConfig, &$errorDetails = null) {
    // Convert single email to array for uniform handling
    $recipients = is_array($to) ? $to : [$to];
    $success = true;
    $errors = [];
    
    // Send to each recipient
    foreach ($recipients as $recipient) {
        // Use SSL context for port 465
        if ($smtpConfig['encryption'] === 'ssl' && $smtpConfig['port'] == 465) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            $socket = @stream_socket_client(
                'ssl://' . $smtpConfig['host'] . ':' . $smtpConfig['port'],
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
        } else {
            $socket = @fsockopen($smtpConfig['host'], $smtpConfig['port'], $errno, $errstr, 30);
        }
        
        if (!$socket) {
            $error = "SMTP Connection Failed: $errstr ($errno)";
            error_log($error);
            $errors[] = $error;
            $success = false;
            continue;
        }
        
        // Read greeting
        $response = fgets($socket, 512);
        $errors[] = "Server greeting: " . trim($response);
        
        // EHLO
        $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        fputs($socket, "EHLO " . $serverName . "\r\n");
        
        // Read all EHLO responses (multiline)
        $ehlo_response = '';
        while ($line = fgets($socket, 512)) {
            $ehlo_response .= trim($line) . ' ';
            if (substr($line, 3, 1) === ' ') break; // Last line has space after code
        }
        $errors[] = "EHLO response: " . trim($ehlo_response);
        
        // Start TLS if needed (for port 587)
        if ($smtpConfig['encryption'] === 'tls' && $smtpConfig['port'] == 587) {
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 512);
            
            if (substr($response, 0, 3) != '220') {
                fclose($socket);
                error_log("STARTTLS failed: $response");
                $success = false;
                continue;
            }
            
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            
            // EHLO again after TLS
            fputs($socket, "EHLO " . $serverName . "\r\n");
            $response = fgets($socket, 512);
        }
        
        // AUTH LOGIN
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 512);
        $errors[] = "AUTH LOGIN response: " . trim($response);
        
        if (substr($response, 0, 3) != '334') {
            $error = "AUTH LOGIN failed (expected 334): " . trim($response);
            fclose($socket);
            error_log($error);
            $errors[] = $error;
            $success = false;
            continue;
        }
        
        fputs($socket, base64_encode($smtpConfig['username']) . "\r\n");
        $response = fgets($socket, 512);
        $errors[] = "Username response: " . trim($response);
        
        if (substr($response, 0, 3) != '334') {
            $error = "Username rejected (expected 334): " . trim($response);
            fclose($socket);
            error_log($error);
            $errors[] = $error;
            $success = false;
            continue;
        }
        
        fputs($socket, base64_encode($smtpConfig['password']) . "\r\n");
        $response = fgets($socket, 512);
        $errors[] = "Password response: " . trim($response);
        
        if (substr($response, 0, 3) != '235') {
            $error = "SMTP Authentication failed (expected 235): " . trim($response);
            fclose($socket);
            error_log($error);
            $errors[] = $error;
            $success = false;
            continue;
        }
        $errors[] = "Authentication successful";
        
        // MAIL FROM
        fputs($socket, "MAIL FROM: <" . $from . ">\r\n");
        $response = fgets($socket, 512);
        
        // RCPT TO
        fputs($socket, "RCPT TO: <" . $recipient . ">\r\n");
        $response = fgets($socket, 512);
        
        // DATA
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 512);
        
        // Email headers and body
        $email = "From: " . $smtpConfig['from_name'] . " <" . $from . ">\r\n";
        $email .= "To: <" . $recipient . ">\r\n";
        $email .= "Subject: " . $subject . "\r\n";
        $email .= "MIME-Version: 1.0\r\n";
        $email .= "Content-Type: text/html; charset=utf-8\r\n";
        $email .= "\r\n";
        $email .= $htmlMessage . "\r\n";
        $email .= ".\r\n";
        
        fputs($socket, $email);
        $response = fgets($socket, 512);
        
        if (substr($response, 0, 3) != '250') {
            $errors[] = "Email sending failed: " . trim($response);
            $success = false;
        } else {
            $errors[] = "Email sent successfully";
        }
        
        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);
    }
    
    if ($errorDetails !== null) {
        $errorDetails = $errors;
    }
    
    return $success;
}

/**
 * Universal send function - uses configured method
 */
function sendFIMEmail($to, $from, $subject, $htmlMessage, &$errorDetails = null) {
    $emailConfig = require __DIR__ . '/email_config.php';
    
    if ($emailConfig['email_method'] === 'smtp') {
        return sendEmailSMTP($to, $from, $subject, $htmlMessage, $emailConfig['smtp'], $errorDetails);
    } else {
        return sendEmailPHPMail($to, $from, $subject, $htmlMessage);
    }
}
