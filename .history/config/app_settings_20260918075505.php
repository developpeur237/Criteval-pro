<?php
declare(strict_types=1);

const APP_SETTINGS_PATH = __DIR__ . '/app_settings_data.php';

function app_settings_defaults(): array
{
    return [
        'application_name' => 'Criteval_pro',
        'organization' => 'FAO - Bureau Afrique',
        'contact_email' => 'contact@criteval.pro',
        'website' => 'https://criteval.pro',
        'modules' => [
            'apercu' => true,
            'projets' => true,
            'criteres' => true,
            'formulaires' => true,
            'evaluations' => true,
        ],
        'smtp' => [
            'profile' => 'hostinger',
            'transport' => 'auto',
            'host' => 'smtp.hostinger.com',
            'port' => 587,
            'username' => 'noreply@criteval.pro',
            'password' => '',
            'security' => 'tls',
            'from' => 'noreply@criteval.pro',
            'sender' => 'Criteval Pro',
            'timeout' => 15,
        ],
        'security' => [
            'session_timeout' => 60,
            'otp_attempts' => 3,
            'logs_enabled' => true,
            'two_factor_enabled' => false,
            'csrf_protection' => true,
        ],
        'notifications' => [
            'enabled' => true,
            'schedule_alerts' => true,
            'browser_alerts' => false,
            'sound_alerts' => false,
            'lead_minutes' => 1440,
            'training_lead_days' => 7,
        ],
    ];
}

function app_settings(): array
{
    if (!is_file(APP_SETTINGS_PATH)) {
        return app_settings_defaults();
    }

    $settings = include APP_SETTINGS_PATH;
    if (!is_array($settings)) {
        return app_settings_defaults();
    }

    return array_replace_recursive(app_settings_defaults(), $settings);
}

function save_app_settings(array $settings): bool
{
    $defaults = app_settings_defaults();
    $settings = array_replace_recursive($defaults, $settings);

    if (!is_dir(dirname(APP_SETTINGS_PATH))) {
        return false;
    }

    $content = "<?php\nreturn " . var_export($settings, true) . ";\n";
    return file_put_contents(APP_SETTINGS_PATH, $content, LOCK_EX) !== false;
}
