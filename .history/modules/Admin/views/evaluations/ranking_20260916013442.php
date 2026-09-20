<?php declare(strict_types=1); ?>
<main class="stub-page admin-stub">
  <h1>Classements des organisations</h1>
  <p>Classement par note totale. À égalité, la première soumission horodatée est prioritaire.</p>
  <table class="data-table"><thead><tr><th>Rang</th><th>Organisation</th><th>Pays</th><th>Note</th><th>Soumis le</th></tr></thead><tbody>
  <?php foreach (($rankings ?? []) as $ranking): ?><tr><td><?= (int) $ranking['rank_position'] ?></td><td><?= e($ranking['organization'] ?? '—') ?></td><td><?= e($ranking['country_code'] ?? '—') ?></td><td><?= number_format((float) $ranking['score'], 2, ',', ' ') ?></td><td><?= e($ranking['submitted_at']) ?></td></tr><?php endforeach; ?>
  </tbody></table>
</main>
<style>.admin-stub{max-width:1100px;margin:30px auto;padding:24px}.data-table{width:100%;border-collapse:collapse;background:#fff}.data-table th,.data-table td{padding:12px;text-align:left;border-bottom:1px solid #dde1e7}</style>
