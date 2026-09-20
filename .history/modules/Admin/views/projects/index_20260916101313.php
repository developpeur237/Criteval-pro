<?php declare(strict_types=1); ?>
<main class="stub-page admin-stub">
  <h1>Organisations</h1>
  <p>Référentiel des organisations évaluées et de leurs caractéristiques.</p>
  <?php $editing = !empty($editProject); ?>
  <form method="post" action="<?= BASE_URL ?>/admin/projects" class="admin-form" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int) ($editProject['id'] ?? 0) ?>">
    <h2><?= $editing ? 'Modifier l’organisation' : 'Créer une organisation' ?></h2>
    <div class="form-grid">
      <label>Nom de l’organisation *<input required name="organization" value="<?= e($editProject['organization'] ?? '') ?>"></label>
      <label>Nom de la fiche *<input required name="title" placeholder="Organisation CS4ME" value="<?= e($editProject['title'] ?? '') ?>"></label>
      <label>Nom du responsable<input name="contact_name" value="<?= e($editProject['contact_name'] ?? '') ?>"></label>
      <label>Téléphone<input type="tel" name="contact_phone" value="<?= e($editProject['contact_phone'] ?? '') ?>"></label>
      <label>E-mail de contact<input type="email" name="contact_email" value="<?= e($editProject['contact_email'] ?? '') ?>"></label>
      <label>Site web<input type="url" name="website" placeholder="https://..." value="<?= e($editProject['website'] ?? '') ?>"></label>
      <label>Domaines thématiques<input name="domains" placeholder="Femmes, jeunesse, santé" value="<?= e($editProject['domains'] ?? '') ?>"></label>
      <label>Publics cibles<input name="target_audiences" placeholder="Femmes, hommes, enfants, adolescents" value="<?= e($editProject['target_audiences'] ?? '') ?>"></label>
      <label>Statut juridique<select name="legal_status"><option value="legal" <?= ($editProject['legal_status'] ?? '') === 'legal' ? 'selected' : '' ?>>Légale</option><option value="non_legal" <?= ($editProject['legal_status'] ?? 'non_legal') === 'non_legal' ? 'selected' : '' ?>>Non légale</option></select></label>
      <label>Pays d’appartenance<input required name="country_code" maxlength="5" placeholder="ML" value="<?= e($editProject['country_code'] ?? '') ?>"></label>
      <label>Nombre de projets<input type="number" min="0" name="project_count" value="<?= e((string) ($editProject['project_count'] ?? '')) ?>"></label>
      <label>Zone d’intervention<input name="intervention_zone" value="<?= e($editProject['intervention_zone'] ?? '') ?>"></label>
      <label>Budget annuel / demandé (FCFA)<input type="number" min="0" step="0.01" name="budget_requested" value="<?= e((string) ($editProject['budget_requested'] ?? '')) ?>"></label>
      <label>Durée d’activité / projet (mois)<input type="number" min="1" name="duration_months" value="<?= e((string) ($editProject['duration_months'] ?? '')) ?>"></label>
      <label>Statut<select name="status"><?php foreach (['draft' => 'Brouillon', 'active' => 'Active', 'closed' => 'Clôturée', 'archived' => 'Archivée'] as $key => $label): ?><option value="<?= $key ?>" <?= ($editProject['status'] ?? 'draft') === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></label>
      <label>Logo de l’organisation<input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif"><small>JPG, PNG, WEBP ou GIF, 2 Mo maximum.</small></label>
    </div>
    <label>Présentation / description détaillée<textarea name="description" rows="5" placeholder="Présentez les activités, réalisations et résultats de votre organisation."><?= e($editProject['description'] ?? '') ?></textarea></label>
    <?php if (!empty($editProject['logo_path'])): ?><p class="current-logo"><img src="<?= BASE_URL . '/' . e($editProject['logo_path']) ?>" alt="Logo actuel"> Logo actuel conservé si aucun nouveau fichier n’est choisi.</p><?php endif; ?>
    <button class="btn btn-secondary" type="submit"><?= $editing ? 'Enregistrer les modifications' : 'Ajouter l’organisation' ?></button>
    <?php if ($editing): ?><a class="btn btn-link" href="<?= BASE_URL ?>/admin/projects">Annuler</a><?php endif; ?>
  </form>
  <h2>Organisations enregistrées</h2>
  <table class="data-table"><thead><tr><th>Organisation</th><th>Domaines</th><th>Cibles</th><th>Pays</th><th>Statut</th><th></th></tr></thead><tbody>
  <?php foreach (($projects ?? []) as $organization): ?>
    <tr><td><?= e($organization['organization'] ?: $organization['title']) ?></td><td><?= e($organization['domains'] ?? '—') ?></td><td><?= e($organization['target_audiences'] ?? '—') ?></td><td><?= e($organization['country_code'] ?? '—') ?></td><td><?= e(($organization['legal_status'] ?? 'non_legal') === 'legal' ? 'Légale' : 'Non légale') ?></td><td><a class="btn btn-link" href="<?= BASE_URL ?>/admin/projects?edit=<?= (int) $organization['id'] ?>">Modifier</a></td></tr>
  <?php endforeach; ?>
  </tbody></table>
</main>
<style>.admin-stub{max-width:1100px;margin:30px auto;padding:24px}.admin-form{margin:20px 0 32px;padding:20px;background:#fff;border:1px solid #dde1e7;border-radius:12px}.form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.admin-form label{display:flex;flex-direction:column;gap:6px;margin-bottom:14px;color:#2c3e50;font-weight:600}.admin-form input,.admin-form select,.admin-form textarea{padding:10px;border:1px solid #dde1e7;border-radius:8px;font:inherit;font-weight:400}.admin-form small{font-weight:400;color:#667085}.current-logo{display:flex;align-items:center;gap:12px;color:#667085}.current-logo img{width:56px;height:56px;object-fit:contain;border:1px solid #dde1e7;border-radius:8px;padding:4px}.btn-link{display:inline-block;margin-left:10px;color:#2c3e50;text-decoration:none}.data-table{width:100%;border-collapse:collapse;background:#fff}.data-table th,.data-table td{padding:12px;text-align:left;border-bottom:1px solid #dde1e7}@media(max-width:700px){.form-grid{grid-template-columns:1fr}}</style>
