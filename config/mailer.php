<?php
declare(strict_types=1);

require_once __DIR__ . '/app_settings.php';

$smtpSettings = app_settings()['smtp'];

define('MAIL_TRANSPORT', $smtpSettings['transport'] ?? 'auto');
define('SMTP_HOST', $smtpSettings['host'] ?? '');
define('SMTP_USER', $smtpSettings['username'] ?? '');
define('SMTP_PASS', $smtpSettings['password'] ?? '');
define('SMTP_PORT', (int) ($smtpSettings['port'] ?? 587));
define('SMTP_SECURE', $smtpSettings['security'] ?? 'tls');
define('SMTP_TIMEOUT', (int) ($smtpSettings['timeout'] ?? 15));
define('MAIL_FROM', $smtpSettings['from'] ?? (app_settings()['contact_email'] ?? ''));
define('MAIL_FROM_NAME', $smtpSettings['sender'] ?? 'Criteval Pro');
