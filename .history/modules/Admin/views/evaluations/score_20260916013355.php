<?php declare(strict_types=1); ?>
<main class="stub-page admin-stub">
  <h1>Évaluation par critères</h1>
  <p>Attribuez une note à chaque critère en vous appuyant sur les preuves fournies par l’organisation.</p>
  <?php foreach (($submissions ?? []) as $submission): ?>
    <form method="post" action="<?= BASE_URL ?>/admin/evaluations/score" class="evaluation-card">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="submission_id" value="<?= (int) $submission['id'] ?>">
      <h2><?= e($submission['organization'] ?? $submission['candidate_name']) ?></h2><p>Soumis le <?= e($submission['submitted_at']) ?></p>
      <?php foreach (($criteria ?? []) as $criterion): ?><label><?= (int) $criterion['order_index'] ?>. <?= e($criterion['label']) ?> <input type="number" name="scores[<?= (int) $criterion['id'] ?>]" min="0" max="<?= e((string) $criterion['max_score']) ?>" step="0.5" required></label><?php endforeach; ?>
      <button class="btn btn-secondary">Enregistrer l’évaluation</button>
    </form>
  <?php endforeach; ?>
</main>
<style>.admin-stub{max-width:1000px;margin:30px auto;padding:24px}.evaluation-card{background:#fff;border:1px solid #dde1e7;border-radius:12px;padding:20px;margin:16px 0}.evaluation-card label{display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #dde1e7}.evaluation-card input{width:90px;padding:8px;border:1px solid #dde1e7;border-radius:8px}</style>
