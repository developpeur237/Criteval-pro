<?php
declare(strict_types=1);

$title = $title ?? 'Administration - ' . APP_NAME;
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
</head>
<body class="admin-layout">
  <?= $content ?>
</body>
</html>
