<?php
/**
 * Email Configuration for FIM System
 * 
 * Choose your email method:
 * - 'php_mail': Use PHP's built-in mail() function (simple but often doesn't work)
 * - 'smtp': Use SMTP (more reliable, works with Gmail, etc.)
 */

return [
    // Email sending method: 'php_mail' or 'smtp'
    'email_method' => 'smtp',  // Using SMTP for reliability
    
    // SMTP Settings (only needed if email_method is 'smtp')
    'smtp' => [
        'host' => 'smtp.hostinger.com',
        'port' => 465,
        'username' => 'your-email@yourdomain.com',
        'password' => 'your-email-password',
        'encryption' => 'ssl',
        'from_name' => 'FIM Monitoring System'
    ],
    
    // Alternative SMTP providers (uncomment and configure as needed):
    
    // Hostinger SMTP
    // 'smtp' => [
    //     'host' => 'smtp.hostinger.com',
    //     'port' => 587,
    //     'username' => 'your-email@yourdomain.com',
    //     'password' => 'your-email-password',
    //     'encryption' => 'tls',
    //     'from_name' => 'FIM Monitor'
    // ],
    
    // SendGrid SMTP
    // 'smtp' => [
    //     'host' => 'smtp.sendgrid.net',
    //     'port' => 587,
    //     'username' => 'apikey',
    //     'password' => 'your-sendgrid-api-key',
    //     'encryption' => 'tls',
    //     'from_name' => 'FIM Monitor'
    // ],
];
