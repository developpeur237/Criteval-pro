<?php declare(strict_types=1); ?>
<main class="stub-page admin-stub">
  <h1>Sessions de formation</h1>
  <p>Planifiez une session avec son objectif et une URL publique partageable.</p>
  <form method="post" action="<?= BASE_URL ?>/admin/sessions" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>Nom de la session *<input required name="name"></label>
    <label>Objectif de la formation *<textarea required name="objective" rows="3"></textarea></label>
    <label>Date *<input required type="date" name="session_date"></label>
    <label>Organisation / évaluation liée *<select required name="project_id"><option value="">Sélectionner une organisation</option><?php foreach (($projects ?? []) as $project): ?><option value="<?= (int) $project['id'] ?>"><?= e((string) ($project['organization'] ?: $project['title'])) ?></option><?php endforeach; ?></select></label>
    <label>Capacité maximale<input type="number" name="capacity" min="1" value="50"></label>
    <label>Format<select name="format"><option value="hybride">Hybride</option><option value="presentiel">Présentiel</option><option value="en_ligne">En ligne</option></select></label>
    <button class="btn btn-secondary">Planifier la session</button>
  </form>
  <table class="data-table"><thead><tr><th>Session</th><th>Objectif</th><th>Date</th><th>URL</th></tr></thead><tbody>
  <?php foreach (($sessions ?? []) as $session): ?><tr><td><?= e($session['name']) ?></td><td><?= e($session['objective']) ?></td><td><?= e($session['session_date']) ?></td><td><a href="<?= BASE_URL ?>/session/<?= e($session['public_slug']) ?>" target="_blank"><?= BASE_URL ?>/session/<?= e($session['public_slug']) ?></a></td></tr><?php endforeach; ?>
  </tbody></table>
</main>
<style>.admin-stub{max-width:1100px;margin:30px auto;padding:24px}.admin-form{margin:20px 0 32px;padding:20px;background:#fff;border:1px solid #dde1e7;border-radius:12px}.admin-form label{display:flex;flex-direction:column;gap:6px;margin-bottom:14px;color:#2c3e50;font-weight:600}.admin-form input,.admin-form textarea,.admin-form select{padding:10px;border:1px solid #dde1e7;border-radius:8px;font:inherit;font-weight:400}.data-table{width:100%;border-collapse:collapse;background:#fff}.data-table th,.data-table td{padding:12px;text-align:left;border-bottom:1px solid #dde1e7;vertical-align:top}</style>
