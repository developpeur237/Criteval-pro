<?php declare(strict_types=1); ?>
<main class="stub-page admin-stub">
  <h1>Critères CS4ME</h1>
  <p>Chaque organisation fournit des preuves documentaires, photos ou rapports pour chaque critère.</p>
  <table class="data-table"><thead><tr><th>#</th><th>Critère</th><th>Preuve attendue</th><th>Poids</th><th>Note maximale</th></tr></thead><tbody>
  <?php foreach (($criteria ?? []) as $criterion): ?>
    <tr><td><?= (int) $criterion['order_index'] ?></td><td><?= e($criterion['label']) ?></td><td><?= e($criterion['description'] ?? '') ?></td><td><?= e((string) $criterion['weight']) ?></td><td><?= e((string) $criterion['max_score']) ?></td></tr>
  <?php endforeach; ?>
  </tbody></table>
</main>
<style>.admin-stub{max-width:1200px;margin:30px auto;padding:24px}.data-table{width:100%;border-collapse:collapse;background:#fff}.data-table th,.data-table td{padding:12px;text-align:left;border-bottom:1px solid #dde1e7;vertical-align:top}</style>
