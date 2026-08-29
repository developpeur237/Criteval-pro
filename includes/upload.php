<?php
declare(strict_types=1);

function upload_path(string $folder, string $filename): string
{
    $safeFolder = preg_replace('/[^a-zA-Z0-9_-]/', '', $folder);
    $safeFile = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return APP_ROOT . '/uploads/' . $safeFolder . '/' . $safeFile;
}
