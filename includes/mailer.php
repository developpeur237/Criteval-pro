<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Central mail service for Criteval Pro.
 *
 * SMTP remains server-side so passwords are never exposed to JavaScript.
 * Browser code can trigger mail through authenticated PHP endpoints only.
 */
function mailer_bootstrap(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $autoload = APP_ROOT . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }
}

function mailer_settings(): array
{
    $settings = app_settings()['smtp'] ?? [];
    $contactEmail = trim((string) (app_settings()['contact_email'] ?? ''));
    $username = trim((string) ($settings['username'] ?? ''));
    $from = trim((string) ($settings['from'] ?? $contactEmail));

    return [
        'transport' => strtolower((string) ($settings['transport'] ?? 'auto')),
        'host' => trim((string) ($settings['host'] ?? '')),
        'port' => max(1, min(65535, (int) ($settings['port'] ?? 587))),
        'username' => $username,
        'password' => (string) ($settings['password'] ?? ''),
        'security' => strtolower((string) ($settings['security'] ?? 'tls')),
        'sender' => trim((string) ($settings['sender'] ?? 'Criteval Pro')),
        'from' => filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : $username,
        'reply_to' => filter_var($contactEmail, FILTER_VALIDATE_EMAIL) ? $contactEmail : $username,
        'timeout' => max(5, min(60, (int) ($settings['timeout'] ?? 15))),
    ];
}

function send_app_email($to, string $subject, string $html, ?string $text = null, array $options = []): array
{
    mailer_bootstrap();
    mailer_flush_queue(5);

    $recipients = normalize_email_recipients($to);
    if (!$recipients) {
        return ['success' => false, 'transport' => null, 'message' => 'Adresse destinataire invalide.'];
    }
    if (!is_valid_mail_header($subject)) {
        return ['success' => false, 'transport' => null, 'message' => 'Sujet invalide.'];
    }

    $settings = array_replace(mailer_settings(), $options);
    unset($settings['_queue']);
    $settings['transport'] = strtolower((string) ($settings['transport'] ?? 'auto'));
    $settings['security'] = strtolower((string) ($settings['security'] ?? 'tls'));
    $allowQueue = !array_key_exists('_queue', $options) || (bool) $options['_queue'];
    $from = trim((string) ($settings['from'] ?? ''));
    $fromName = trim((string) ($settings['sender'] ?? APP_NAME));
    $replyTo = trim((string) ($settings['reply_to'] ?? $from));

    if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'transport' => null, 'message' => 'Configurez une adresse expediteur valide.'];
    }
    if ($replyTo !== '' && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $replyTo = $from;
    }

    $text ??= html_to_plain_text($html);
    $message = build_mime_message($recipients, $subject, $html, $text, $from, $fromName, $replyTo);
    $transport = in_array($settings['transport'], ['auto', 'smtp', 'php'], true) ? $settings['transport'] : 'auto';
    $connectivity = mailer_connectivity($settings);
    $errors = [];

    if ($transport !== 'php' && $connectivity['smtp_reachable']) {
        if (class_exists(PHPMailer::class)) {
            try {
                phpmailer_deliver($settings, $from, $fromName, $replyTo, $recipients, $subject, $html, $text);
                return ['success' => true, 'transport' => 'phpmailer-smtp', 'message' => 'Message envoye par SMTP.'];
            } catch (Throwable $phpMailerError) {
                $errors[] = 'PHPMailer SMTP: ' . $phpMailerError->getMessage();
                if ($transport === 'smtp') {
                    error_log('[Criteval mail] ' . implode(' | ', $errors));
                }
            }
        }

        try {
            smtp_deliver($settings, $from, $recipients, $message);
            return ['success' => true, 'transport' => 'smtp', 'message' => 'Message envoye par SMTP.'];
        } catch (Throwable $smtpError) {
            $errors[] = 'SMTP: ' . $smtpError->getMessage();
            if ($transport === 'smtp') {
                error_log('[Criteval mail] ' . implode(' | ', $errors));
            }
        }
    } elseif ($transport !== 'php') {
        $errors[] = 'SMTP: serveur injoignable, mode hors ligne detecte.';
        $settings['timeout'] = min((int) ($settings['timeout'] ?? 15), 5);
    }

    try {
        php_mail_deliver($recipients, $subject, $html, $from, $fromName, $replyTo);
        return ['success' => true, 'transport' => 'php-mail', 'online' => $connectivity['online'], 'message' => 'Message accepte par PHP mail().'];
    } catch (Throwable $mailError) {
        $errors[] = 'PHP mail: ' . $mailError->getMessage();
        error_log('[Criteval mail] ' . implode(' | ', $errors));
        if ($allowQueue) {
            $queued = mailer_queue_email($recipients, $subject, $html, $text);
            if ($queued['success']) {
                return [
                    'success' => true,
                    'transport' => 'queue',
                    'online' => false,
                    'queued' => true,
                    'queue_id' => $queued['id'],
                    'message' => 'Serveur email hors ligne. Message mis en file et sera envoye automatiquement.',
                ];
            }
            $errors[] = 'Queue: ' . $queued['message'];
        }
        return ['success' => false, 'transport' => $transport === 'php' ? 'php-mail' : 'auto', 'online' => $connectivity['online'], 'message' => implode(' | ', $errors)];
    }
}

function send_admin_custom_email($to, string $subject, string $message, bool $isHtml = false): array
{
    $body = trim($message);
    if ($body === '') {
        return ['success' => false, 'transport' => null, 'message' => 'Message email vide.'];
    }

    $html = $isHtml ? $body : nl2br(e($body), false);
    $text = $isHtml ? html_to_plain_text($body) : $body;
    return send_app_email($to, $subject, email_layout($subject, $html), $text);
}

function test_app_email(string $to): array
{
    $settings = mailer_settings();
    $connectivity = mailer_connectivity($settings, true);
    $subject = 'Test de configuration email - Criteval Pro';
    $html = email_layout(
        'Configuration email validee',
        '<p>La configuration email de Criteval Pro fonctionne correctement.</p>'
        . '<p>Serveur : <strong>' . e($settings['host'] ?: 'PHP mail') . '</strong><br>Port : <strong>' . e((string) $settings['port']) . '</strong><br>Mode : <strong>' . e($settings['transport']) . '</strong></p>'
        . '<p>Etat detecte : <strong>' . e($connectivity['online'] ? 'en ligne' : 'hors ligne') . '</strong></p>'
        . '<p style="color:#64748b">Message envoye depuis les parametres administrateur.</p>'
    );
    return send_app_email($to, $subject, $html, 'La configuration email de Criteval Pro fonctionne correctement.');
}

function send_otp_email(string $to, string $otp, ?array $form = null): array
{
    $title = $form['title'] ?? 'formulaire Criteval Pro';
    $html = email_layout(
        'Code de verification',
        '<p>Votre code d acces au ' . e((string) $title) . ' est :</p>'
        . '<p style="font-size:30px;letter-spacing:8px;font-weight:700;margin:24px 0;color:#0f766e">' . e($otp) . '</p>'
        . '<p>Ce code expire dans 10 minutes. Si vous n avez rien demande, ignorez ce message.</p>'
    );
    return send_app_email($to, 'Votre code de verification - Criteval Pro', $html, 'Votre code de verification Criteval Pro est : ' . $otp);
}

function send_submission_confirmation_email(string $to, string $candidateName, ?array $form = null, ?int $submissionId = null): array
{
    $title = $form['title'] ?? 'votre formulaire';
    $reference = $submissionId ? 'CRIT-' . str_pad((string) $submissionId, 6, '0', STR_PAD_LEFT) : null;
    $html = email_layout(
        'Candidature recue',
        '<p>Bonjour ' . e($candidateName !== '' ? $candidateName : 'cher candidat') . ',</p>'
        . '<p>Votre soumission pour <strong>' . e((string) $title) . '</strong> a bien ete recue.</p>'
        . ($reference ? '<p>Reference : <strong>' . e($reference) . '</strong></p>' : '')
        . '<p>Vous recevrez une notification lorsque votre dossier evoluera.</p>'
    );
    return send_app_email($to, 'Confirmation de soumission - Criteval Pro', $html);
}

function mailer_connectivity(?array $settings = null, bool $refresh = false): array
{
    $settings ??= mailer_settings();
    $host = trim((string) ($settings['host'] ?? ''));
    $port = max(1, min(65535, (int) ($settings['port'] ?? 587)));
    $security = strtolower((string) ($settings['security'] ?? 'tls'));
    $cacheKey = $security . '://' . $host . ':' . $port;

    static $cached = [];
    if (isset($cached[$cacheKey]) && !$refresh) {
        return $cached[$cacheKey];
    }

    $smtpReachable = false;
    $error = null;

    if ($host !== '') {
        $target = ($security === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $socket = @stream_socket_client($target, $errno, $errstr, 2, STREAM_CLIENT_CONNECT);
        if (is_resource($socket)) {
            $smtpReachable = true;
            fclose($socket);
        } else {
            $error = trim((string) ($errstr ?: 'Connexion reseau indisponible'));
        }
    } else {
        $error = 'Serveur SMTP non configure.';
    }

    $cached[$cacheKey] = [
        'online' => $smtpReachable,
        'smtp_reachable' => $smtpReachable,
        'php_mail_available' => function_exists('mail'),
        'host' => $host,
        'port' => $port,
        'error' => $error,
    ];
    return $cached[$cacheKey];
}

function mailer_queue_dir(): string
{
    return APP_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'mail_queue';
}

function mailer_queue_email(array $recipients, string $subject, string $html, string $text): array
{
    $dir = mailer_queue_dir();
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return ['success' => false, 'message' => 'Impossible de creer la file email.'];
    }

    $id = date('YmdHis') . '-' . bin2hex(random_bytes(6));
    $payload = [
        'id' => $id,
        'created_at' => date(DATE_ATOM),
        'attempts' => 0,
        'last_error' => null,
        'to' => $recipients,
        'subject' => $subject,
        'html' => $html,
        'text' => $text,
    ];

    $path = $dir . DIRECTORY_SEPARATOR . $id . '.json';
    $written = file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    return $written === false
        ? ['success' => false, 'message' => 'Impossible d enregistrer le message en file.']
        : ['success' => true, 'id' => $id, 'path' => $path];
}

function mailer_flush_queue(int $limit = 10): array
{
    static $flushing = false;
    if ($flushing) {
        return ['sent' => 0, 'failed' => 0];
    }

    $dir = mailer_queue_dir();
    if (!is_dir($dir)) {
        return ['sent' => 0, 'failed' => 0];
    }

    $settings = mailer_settings();
    if (!mailer_connectivity($settings)['smtp_reachable'] && !function_exists('mail')) {
        return ['sent' => 0, 'failed' => 0];
    }

    $flushing = true;
    $sent = 0;
    $failed = 0;
    $files = glob($dir . DIRECTORY_SEPARATOR . '*.json') ?: [];
    sort($files);

    foreach (array_slice($files, 0, max(1, $limit)) as $file) {
        $payload = json_decode((string) file_get_contents($file), true);
        if (!is_array($payload)) {
            @unlink($file);
            continue;
        }

        $result = send_app_email(
            $payload['to'] ?? [],
            (string) ($payload['subject'] ?? ''),
            (string) ($payload['html'] ?? ''),
            (string) ($payload['text'] ?? ''),
            ['_queue' => false]
        );

        if ($result['success'] && ($result['transport'] ?? '') !== 'queue') {
            @unlink($file);
            $sent++;
            continue;
        }

        $payload['attempts'] = (int) ($payload['attempts'] ?? 0) + 1;
        $payload['last_error'] = $result['message'] ?? 'Envoi differe.';
        $payload['last_attempt_at'] = date(DATE_ATOM);
        file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
        $failed++;
    }

    $flushing = false;
    return ['sent' => $sent, 'failed' => $failed];
}

function mailer_queue_count(): int
{
    $dir = mailer_queue_dir();
    if (!is_dir($dir)) {
        return 0;
    }
    return count(glob($dir . DIRECTORY_SEPARATOR . '*.json') ?: []);
}

function phpmailer_deliver(array $settings, string $from, string $fromName, string $replyTo, array $recipients, string $subject, string $html, string $text): void
{
    $host = trim((string) ($settings['host'] ?? ''));
    if ($host === '') {
        throw new RuntimeException('Serveur SMTP non configure.');
    }

    $mail = new PHPMailer(true);
    try {
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = (int) ($settings['port'] ?? 587);
        $mail->Timeout = max(5, (int) ($settings['timeout'] ?? 15));
        $mail->SMTPAutoTLS = true;

        $security = strtolower((string) ($settings['security'] ?? 'tls'));
        if ($security === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($security === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }

        $username = trim((string) ($settings['username'] ?? ''));
        if ($username !== '') {
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = (string) ($settings['password'] ?? '');
        }

        $mail->setFrom($from, $fromName);
        if ($replyTo !== '') {
            $mail->addReplyTo($replyTo);
        }
        foreach ($recipients as $recipient) {
            $mail->addAddress($recipient);
        }
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $html;
        $mail->AltBody = $text;
        $mail->send();
    } catch (PHPMailerException $exception) {
        throw new RuntimeException($exception->getMessage(), 0, $exception);
    }
}

function smtp_deliver(array $settings, string $from, array $recipients, string $message): void
{
    $host = trim((string) ($settings['host'] ?? ''));
    if ($host === '') {
        throw new RuntimeException('Serveur SMTP non configure.');
    }

    $security = in_array($settings['security'], ['ssl', 'tls', 'none'], true) ? $settings['security'] : 'tls';
    $transportHost = $security === 'ssl' ? 'ssl://' . $host : $host;
    $timeout = max(5, (int) ($settings['timeout'] ?? 15));
    $socket = @stream_socket_client($transportHost . ':' . (int) $settings['port'], $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
    if (!is_resource($socket)) {
        throw new RuntimeException('Connexion SMTP impossible (' . $errno . '): ' . $errstr);
    }

    stream_set_timeout($socket, $timeout);
    try {
        smtp_expect($socket, 220);
        smtp_command($socket, 'EHLO ' . smtp_local_name(), 250);
        if ($security === 'tls') {
            smtp_command($socket, 'STARTTLS', 220);
            $crypto = defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')
                ? STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                : STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $crypto |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            }
            // TLS failures must become mail errors, not PHP warnings in JSON responses.
            if (!@stream_socket_enable_crypto($socket, true, $crypto)) {
                throw new RuntimeException('TLS SMTP indisponible.');
            }
            smtp_command($socket, 'EHLO ' . smtp_local_name(), 250);
        }
        if (trim((string) ($settings['username'] ?? '')) !== '') {
            smtp_command($socket, 'AUTH LOGIN', 334);
            smtp_command($socket, base64_encode((string) $settings['username']), 334);
            smtp_command($socket, base64_encode((string) ($settings['password'] ?? '')), 235);
        }
        smtp_command($socket, 'MAIL FROM:<' . smtp_address($from) . '>', 250);
        foreach ($recipients as $recipient) {
            smtp_command($socket, 'RCPT TO:<' . smtp_address($recipient) . '>', [250, 251]);
        }
        smtp_command($socket, 'DATA', 354);
        fwrite($socket, dot_stuff_message($message) . "\r\n.\r\n");
        smtp_expect($socket, 250);
        smtp_command($socket, 'QUIT', 221);
    } finally {
        fclose($socket);
    }
}

function php_mail_deliver(array $recipients, string $subject, string $html, string $from, string $fromName, string $replyTo): void
{
    if (!function_exists('mail')) {
        throw new RuntimeException('La fonction PHP mail() est indisponible.');
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . mail_header($fromName) . ' <' . $from . '>',
        'Reply-To: ' . $replyTo,
        'X-Mailer: Criteval Pro',
    ];
    $accepted = true;
    foreach ($recipients as $recipient) {
        $parameters = PHP_OS_FAMILY === 'Windows' ? '' : '-f' . escapeshellarg($from);
        $accepted = ($parameters !== ''
            ? @mail($recipient, mail_header($subject), $html, implode("\r\n", $headers), $parameters)
            : @mail($recipient, mail_header($subject), $html, implode("\r\n", $headers))) && $accepted;
    }
    if (!$accepted) {
        throw new RuntimeException('PHP mail() a refuse le message.');
    }
}

function build_mime_message(array $recipients, string $subject, string $html, string $text, string $from, string $fromName, string $replyTo): string
{
    $boundary = '=_Criteval_' . bin2hex(random_bytes(12));
    $headers = [
        'Date: ' . date(DATE_RFC2822),
        'From: ' . mail_header($fromName) . ' <' . $from . '>',
        'To: ' . implode(', ', array_map(static fn(string $email): string => '<' . $email . '>', $recipients)),
        'Subject: ' . mail_header($subject),
        'MIME-Version: 1.0',
        'Reply-To: ' . $replyTo,
        'X-Mailer: Criteval Pro',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];

    $body = '--' . $boundary . "\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . normalize_mail_body($text) . "\r\n\r\n"
        . '--' . $boundary . "\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . normalize_mail_body($html) . "\r\n\r\n"
        . '--' . $boundary . '--';

    return implode("\r\n", $headers) . "\r\n\r\n" . $body;
}

function normalize_email_recipients($to): array
{
    $items = is_array($to) ? $to : preg_split('/[,;]/', $to);
    $recipients = [];
    foreach ($items ?: [] as $item) {
        $email = trim((string) $item);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $recipients[strtolower($email)] = $email;
        }
    }
    return array_values($recipients);
}

function email_layout(string $title, string $content): string
{
    $settings = app_settings();
    $appName = e((string) ($settings['application_name'] ?? APP_NAME));
    return '<!doctype html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
        . '<body style="margin:0;background:#f3f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937">'
        . '<div style="max-width:620px;margin:0 auto;padding:28px 14px">'
        . '<div style="background:#ffffff;border:1px solid #dfe7ef;border-radius:10px;overflow:hidden">'
        . '<div style="padding:22px 26px;background:#0f172a;color:#fff"><strong style="font-size:18px">' . $appName . '</strong></div>'
        . '<div style="padding:26px"><h1 style="font-size:22px;margin:0 0 18px;color:#0f172a">' . e($title) . '</h1>' . $content . '</div>'
        . '<div style="padding:16px 26px;background:#f8fafc;color:#64748b;font-size:12px">Email automatique envoye par ' . $appName . '.</div>'
        . '</div></div></body></html>';
}

function html_to_plain_text(string $html): string
{
    $text = html_entity_decode(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html), ENT_QUOTES, 'UTF-8');
    return trim(preg_replace('/[ \t]+/', ' ', preg_replace('/\n{3,}/', "\n\n", $text) ?? $text) ?? '');
}

function normalize_mail_body(string $value): string
{
    return preg_replace('/\r?\n/', "\r\n", $value) ?? $value;
}

function dot_stuff_message(string $message): string
{
    $message = normalize_mail_body($message);
    return preg_replace('/^\./m', '..', $message) ?? $message;
}

function smtp_command($socket, string $command, $expected): string
{
    fwrite($socket, $command . "\r\n");
    return smtp_expect($socket, $expected);
}

function smtp_expect($socket, $expected): string
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') {
            break;
        }
    }
    $meta = stream_get_meta_data($socket);
    if (!empty($meta['timed_out'])) {
        throw new RuntimeException('Delai SMTP depasse.');
    }
    $code = (int) substr($response, 0, 3);
    $accepted = is_array($expected) ? in_array($code, $expected, true) : $code === $expected;
    if (!$accepted) {
        throw new RuntimeException('Reponse SMTP inattendue (' . $code . ').');
    }
    return $response;
}

function smtp_local_name(): string
{
    $name = $_SERVER['SERVER_NAME'] ?? gethostname() ?: 'localhost';
    return preg_replace('/[^A-Za-z0-9.-]/', '', (string) $name) ?: 'localhost';
}

function is_valid_mail_header(string $value): bool
{
    return !preg_match('/[\r\n]/', $value);
}

function mail_header(string $value): string
{
    $value = trim(preg_replace('/[\r\n]+/', ' ', $value) ?? '');
    if ($value === '' || preg_match('/^[\x20-\x7E]+$/', $value)) {
        return $value;
    }
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

function smtp_address(string $email): string
{
    return preg_replace('/[^A-Za-z0-9.!#$%&\'*+\/=?^_`{|}~@-]/', '', $email) ?? '';
}
