<?php declare(strict_types=1); ?>
<main style="max-width:760px;margin:60px auto;padding:32px;background:#fff;border:1px solid #dde1e7;border-radius:12px;font-family:Inter,sans-serif;color:#2c3e50">
  <?php if (!$session): ?><h1>Session introuvable</h1><p>Cette session n’est plus disponible.</p><?php else: ?>
    <p style="color:#2eaf7d;font-weight:600">Session de formation CS4ME</p>
    <h1><?= e($session['name']) ?></h1>
    <p><strong>Date :</strong> <?= e($session['session_date']) ?></p>
    <h2>Objectif</h2><p><?= nl2br(e($session['objective'])) ?></p>
  <?php endif; ?>
</main>
