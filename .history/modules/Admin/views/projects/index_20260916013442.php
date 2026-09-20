<?php declare(strict_types=1); ?>
<main class="stub-page admin-stub">
  <h1>Organisations</h1>
  <p>Référentiel des organisations évaluées et de leurs caractéristiques.</p>
  <form method="post" action="<?= BASE_URL ?>/admin/projects" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="form-grid">
      <label>Nom de l’organisation *<input required name="organization"></label>
      <label>Nom de la fiche *<input required name="title" placeholder="Organisation CS4ME"></label>
      <label>Domaines thématiques<input name="domains" placeholder="Femmes, jeunesse, santé"></label>
      <label>Publics cibles<input name="target_audiences" placeholder="Femmes, hommes, enfants, adolescents"></label>
      <label>Statut juridique<select name="legal_status"><option value="legal">Légale</option><option value="non_legal">Non légale</option></select></label>
      <label>Pays d’appartenance<input required name="country_code" maxlength="5" placeholder="ML"></label>
      <label>Nombre de projets (facultatif)<input type="number" min="0" name="project_count"></label>
      <label>Zone d’intervention<input name="intervention_zone"></label>
    </div>
    <label>Description<textarea name="description" rows="3"></textarea></label>
    <button class="btn btn-secondary" type="submit">Ajouter l’organisation</button>
  </form>
  <h2>Organisations enregistrées</h2>
  <table class="data-table"><thead><tr><th>Organisation</th><th>Domaines</th><th>Cibles</th><th>Pays</th><th>Statut</th></tr></thead><tbody>
  <?php foreach (($projects ?? []) as $organization): ?>
    <tr><td><?= e($organization['organization'] ?: $organization['title']) ?></td><td><?= e($organization['domains'] ?? '—') ?></td><td><?= e($organization['target_audiences'] ?? '—') ?></td><td><?= e($organization['country_code'] ?? '—') ?></td><td><?= e(($organization['legal_status'] ?? 'non_legal') === 'legal' ? 'Légale' : 'Non légale') ?></td></tr>
  <?php endforeach; ?>
  </tbody></table>
</main>
<style>.admin-stub{max-width:1100px;margin:30px auto;padding:24px}.admin-form{margin:20px 0 32px;padding:20px;background:#fff;border:1px solid #dde1e7;border-radius:12px}.form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.admin-form label{display:flex;flex-direction:column;gap:6px;margin-bottom:14px;color:#2c3e50;font-weight:600}.admin-form input,.admin-form select,.admin-form textarea{padding:10px;border:1px solid #dde1e7;border-radius:8px;font:inherit;font-weight:400}.data-table{width:100%;border-collapse:collapse;background:#fff}.data-table th,.data-table td{padding:12px;text-align:left;border-bottom:1px solid #dde1e7}@media(max-width:700px){.form-grid{grid-template-columns:1fr}}</style>
