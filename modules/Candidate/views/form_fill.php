<?php
$candidateEmail = (string) ($_SESSION['candidate_email'] ?? '');
$candidateFormId = (int) ($_SESSION['candidate_form_id'] ?? 1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Formulaire Candidat — Criteval_pro</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
<style>
  .candidate-dynamic-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:18px}
  .candidate-dynamic-grid .candidate-field{min-width:0}
  .dynamic-block{grid-column:1/-1}.dynamic-block h2,.dynamic-block h3{margin:0 0 8px}.dynamic-block p{margin:0;color:#667085;line-height:1.6}.dynamic-block hr{border:0;border-top:1px solid #dde1e7}
  .dynamic-options{display:flex;flex-wrap:wrap;gap:12px}.dynamic-options label{font-weight:500;color:#344054}
  @media(max-width:700px){.candidate-dynamic-grid{grid-template-columns:1fr}.candidate-dynamic-grid>*{grid-column:1!important}}
</style>
</head>
<body>
<div id="page-candidate" class="candidate-page">
  <div class="candidate-shell">
    <header class="candidate-topbar">
      <div class="candidate-topbar-brand">
        <div class="candidate-logo"><i class="fas fa-chart-line"></i></div>
        <div>
          <div class="candidate-brand-title">Criteval<em>_pro</em></div>
          <div class="candidate-topline">Candidature moderne, organisée comme une vraie application.</div>
        </div>
      </div>
      <div class="candidate-topbar-actions">
        <div class="candidate-pill">
          <strong>Progression du formulaire</strong>
          <div class="candidate-progress-bar"><div class="candidate-progress-fill"></div></div>
        </div>
        <div class="candidate-pill">
          <span><i class="fas fa-check-circle"></i> Sauvegardé à 14:32</span>
          <button class="btn btn-ghost btn-sm candidate-return" onclick="window.location.href='<?= BASE_URL ?>/'"><i class="fas fa-arrow-left"></i> Retour</button>
        </div>
      </div>
    </header>

    <?php
      $layoutItems = [];
      if (!empty($candidateForm['layout_json'])) {
          $decodedLayout = json_decode((string) $candidateForm['layout_json'], true);
          $layoutItems = is_array($decodedLayout) ? $decodedLayout : [];
      }
      $fieldName = static function (array $field, int $index): string {
          $name = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) ($field['name'] ?? 'champ_' . $index));
          return trim((string) $name, '_') ?: 'champ_' . $index;
      };
      $fieldOptions = static function (array $field): array {
          $raw = preg_split('/\r?\n/', (string) ($field['options'] ?? '')) ?: [];
          return array_values(array_filter(array_map('trim', $raw), static fn ($value): bool => $value !== ''));
      };
    ?>
    <?php if ($layoutItems): ?>
    <form class="candidate-body" id="candidateDynamicForm" enctype="multipart/form-data">
      <main class="candidate-column-main">
        <section class="candidate-card candidate-card--hero">
          <div class="candidate-meta-row"><span class="candidate-tag"><i class="fas fa-circle"></i> Formulaire ouvert</span><span class="candidate-muted">Organisation</span></div>
          <h1><?= e($candidateForm['title'] ?? 'Dossier de candidature') ?></h1>
          <p><?= e($candidateForm['description'] ?? 'Complétez les informations et joignez les preuves demandées pour chaque critère.') ?></p>
        </section>
        <section class="candidate-card candidate-dynamic-grid">
          <?php foreach ($layoutItems as $index => $field): $type = (string) ($field['type'] ?? 'text'); $name = $fieldName((array) $field, (int) $index); $label = (string) ($field['label'] ?? 'Champ'); $required = !empty($field['required']); $options = $fieldOptions((array) $field); ?>
            <?php if (in_array($type, ['heading', 'paragraph', 'divider', 'section'], true)): ?>
              <div class="dynamic-block dynamic-<?= e($type) ?>" style="grid-column:span <?= max(1, min(12, (int) ($field['span'] ?? 12))) ?>">
                <?php if ($type === 'heading'): ?><h2><?= e($label) ?></h2><?php elseif ($type === 'paragraph'): ?><p><?= e((string) ($field['placeholder'] ?? $label)) ?></p><?php elseif ($type === 'divider'): ?><hr><?php else: ?><h3><?= e($label) ?></h3><p><?= e((string) ($field['placeholder'] ?? '')) ?></p><?php endif; ?>
              </div>
            <?php else: ?>
              <div class="candidate-field" style="grid-column:span <?= max(1, min(12, (int) ($field['span'] ?? 6))) ?>">
                <label class="candidate-label" for="field-<?= e($name) ?>"><?= e($label) ?><?php if ($required): ?> <span class="req">*</span><?php endif; ?></label>
                <?php if ($type === 'textarea'): ?><textarea id="field-<?= e($name) ?>" class="form-textarea" name="<?= e($name) ?>" placeholder="<?= e((string) ($field['placeholder'] ?? '')) ?>" <?= $required ? 'required' : '' ?>></textarea>
                <?php elseif ($type === 'select'): ?><select id="field-<?= e($name) ?>" class="form-input" name="<?= e($name) ?>" <?= $required ? 'required' : '' ?>><option value="">Choisir…</option><?php foreach ($options as $option): ?><option value="<?= e($option) ?>"><?= e($option) ?></option><?php endforeach; ?></select>
                <?php elseif (in_array($type, ['checkbox', 'radio'], true)): ?><div class="dynamic-options"><?php foreach (($options ?: ['Oui', 'Non']) as $option): ?><label><input type="<?= $type === 'radio' ? 'radio' : 'checkbox' ?>" name="<?= e($type === 'radio' ? $name : $name . '[]') ?>" value="<?= e($option) ?>" <?= $required ? 'required' : '' ?>> <?= e($option) ?></label><?php endforeach; ?></div>
                <?php else: ?><input id="field-<?= e($name) ?>" class="form-input" type="<?= in_array($type, ['email', 'number', 'date', 'file'], true) ? e($type) : 'text' ?>" name="<?= e($name) ?>" placeholder="<?= e((string) ($field['placeholder'] ?? '')) ?>" <?= $required ? 'required' : '' ?>><?php endif; ?>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </section>
        <section class="candidate-card"><div class="candidate-section-title"><span class="candidate-step">E</span> Preuves par critère</div><p class="candidate-sidebar-text">Ajoutez les documents, photos ou rapports qui justifient chaque critère.</p>
          <?php foreach (($candidateCriteria ?? []) as $criterion): ?><div class="candidate-upload"><label class="candidate-label" for="evidence-<?= (int) $criterion['id'] ?>"><?= (int) $criterion['order_index'] ?>. <?= e($criterion['label']) ?><?php if (!empty($criterion['is_required'])): ?> <span class="req">*</span><?php endif; ?></label><input id="evidence-<?= (int) $criterion['id'] ?>" type="file" name="evidence[<?= (int) $criterion['id'] ?>]" accept="application/pdf,image/jpeg,image/png" <?= !empty($criterion['is_required']) ? 'required' : '' ?>><p class="candidate-upload-hint"><?= e($criterion['description'] ?? '') ?></p></div><?php endforeach; ?>
        </section>
      </main>
      <aside class="candidate-column-side"><div class="candidate-card candidate-card--panel"><div class="candidate-section-title"><span class="candidate-step">A</span> Résumé</div><p class="candidate-sidebar-text">Les champs obligatoires et les preuves requises doivent être complétés avant l’envoi.</p></div></aside>
    </form>
    <?php else: ?>
    <div class="candidate-body">
      <main class="candidate-column-main">
        <section class="candidate-card candidate-card--hero">
          <div class="candidate-meta-row">
            <span class="candidate-tag"><i class="fas fa-circle"></i> Formulaire ouvert</span>
            <span class="candidate-muted">Clôture : 15 décembre 2025</span>
          </div>
          <h1>Dossier de candidature — Programme AGRI-2025</h1>
          <p>Ce formulaire vous permet de soumettre votre dossier de candidature au Programme d'Agriculture Durable 2025. L'interface a été pensée pour rester claire, symétrique et fluide sur bureau comme sur mobile.</p>
          <div class="candidate-meta-row candidate-meta-row--details">
            <span><i class="fas fa-clock"></i> Environ 20 minutes</span>
            <span><i class="fas fa-globe-africa"></i> Afrique subsaharienne</span>
            <span><i class="fas fa-file-pdf"></i> PDF requis</span>
          </div>
        </section>

        <section class="candidate-card">
          <div class="candidate-section-title"><span class="candidate-step">1</span> Informations de l’organisation</div>
          <div class="candidate-form-grid">
            <div class="candidate-field">
              <label class="candidate-label">Nom complet <span class="req">*</span></label>
              <input type="text" class="form-input" name="candidate_name" placeholder="Ex: Jean-Paul Kamga" value="Amara Konaté">
            </div>
            <div class="candidate-field">
              <label class="candidate-label">Organisation <span class="req">*</span></label>
              <input type="text" class="form-input" placeholder="Nom de votre organisation" value="Coop. Agricole du Poro">
            </div>
            <div class="candidate-field">
              <label class="candidate-label">Pays <span class="req">*</span></label>
              <select class="form-input" name="country_code">
                <option>🇨🇮 Côte d'Ivoire</option>
                <option>🇨🇲 Cameroun</option>
                <option>🇸🇳 Sénégal</option>
                <option>🇲🇱 Mali</option>
              </select>
            </div>
            <div class="candidate-field">
              <label class="candidate-label">Téléphone</label>
              <input type="tel" class="form-input" placeholder="+225 01 23 45 67 89" value="+225 07 45 23 89 01">
            </div>
          </div>
        </section>

        <section class="candidate-card">
          <div class="candidate-section-title"><span class="candidate-step">2</span> Présentation de l’organisation</div>
          <div class="candidate-field">
            <label class="candidate-label">Nom de l’organisation <span class="req">*</span></label>
            <input type="text" class="form-input" value="Système d'irrigation solaire pour les petits agriculteurs">
          </div>
          <div class="candidate-field">
            <label class="candidate-label">Description détaillée <span class="req">*</span></label>
            <textarea class="form-textarea">Présentez les activités, réalisations et résultats de votre organisation.</textarea>
          </div>
          <div class="candidate-form-grid">
            <div class="candidate-field">
              <label class="candidate-label">Budget demandé (FCFA) <span class="req">*</span></label>
              <input type="number" class="form-input" value="45000000">
            </div>
            <div class="candidate-field">
              <label class="candidate-label">Ancienneté de l’organisation</label>
              <select class="form-input" name="duration">
                <option>24 mois</option>
                <option>12 mois</option>
                <option>36 mois</option>
              </select>
            </div>
          </div>
        </section>

        <section class="candidate-card">
          <div class="candidate-section-title"><span class="candidate-step">3</span> Preuves par critère</div>
          <p class="candidate-sidebar-text">Ajoutez un document, une photo ou un rapport pour chaque réalisation. PDF, JPG et PNG, 10 Mo maximum par fichier.</p>
          <?php foreach (($candidateCriteria ?? []) as $criterion): ?>
            <div class="candidate-upload">
              <label class="candidate-label"><?= (int) $criterion['order_index'] ?>. <?= e($criterion['label']) ?> <span class="req">*</span></label>
              <input type="file" name="evidence[<?= (int) $criterion['id'] ?>]" accept="application/pdf,image/jpeg,image/png" required>
              <p class="candidate-upload-hint"><?= e($criterion['description'] ?? '') ?></p>
            </div>
          <?php endforeach; ?>
        </section>
      </main>

      <aside class="candidate-column-side">
        <div class="candidate-card candidate-card--panel">
          <div class="candidate-section-title"><span class="candidate-step">A</span> Résumé rapide</div>
          <ul class="candidate-list">
            <li><i class="fas fa-circle"></i> Formulaire actif</li>
            <li><i class="fas fa-circle"></i> Clôture : 15 déc 2025</li>
            <li><i class="fas fa-circle"></i> Durée estimée : 20 min</li>
          </ul>
          <p class="candidate-sidebar-text">Ajoutez les informations de l’organisation et une preuve pour chaque critère avant de soumettre.</p>
        </div>

        <div class="candidate-card candidate-card--panel candidate-progress-card">
          <strong>Progression du formulaire</strong>
          <div class="candidate-progress-bar"><div class="candidate-progress-fill"></div></div>
          <p class="candidate-sidebar-text">65% complété — complétez les dernières étapes pour finaliser votre dossier.</p>
        </div>
      </aside>
    </div>
    <?php endif; ?>

    <footer class="candidate-footer">
      <button class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Section précédente</button>
      <input type="hidden" id="candidateEmail" value="<?= e($candidateEmail) ?>">
      <input type="hidden" id="candidateFormId" value="<?= e((string) $candidateFormId) ?>">
      <button class="btn btn-secondary btn-lg" type="button" id="candidateSubmitBtn">
        <i class="fas fa-paper-plane"></i> Soumettre ma candidature
      </button>
    </footer>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>window.CRITEVAL_BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/app.js?v=20260611-login-route"></script>
<script>
(function () {
  const submitButton = document.getElementById('candidateSubmitBtn');
  if (!submitButton) return;
  submitButton.addEventListener('click', async () => {
    const email = document.getElementById('candidateEmail')?.value || '';
    if (!email) {
      window.location.href = '<?= BASE_URL ?>/formulaire';
      return;
    }

    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Soumission...';
    const form = document.getElementById('candidateDynamicForm');
    if (form && !form.reportValidity()) {
      submitButton.disabled = false;
      submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Soumettre ma candidature';
      return;
    }
    const data = form ? new FormData(form) : new FormData();
    data.set('email', email);
    data.set('form_id', document.getElementById('candidateFormId')?.value || '1');
    if (!data.has('candidate_name')) data.set('candidate_name', document.querySelector('[name="candidate_name"]')?.value || '');
    if (!data.has('country_code')) data.set('country_code', document.querySelector('[name="country_code"]')?.value || '');
    if (!form) document.querySelectorAll('[name^="evidence["]').forEach((input) => { if (input.files[0]) data.append(input.name, input.files[0]); });

    try {
      const response = await fetch('<?= BASE_URL ?>/candidate/submit', { method: 'POST', body: data });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || 'Soumission impossible.');
      showToast(result.message || 'Candidature soumise.', result.mail && !result.mail.success ? 'warning' : 'success');
      setTimeout(() => { window.location.href = '<?= BASE_URL ?>/'; }, 1200);
    } catch (error) {
      showToast(error.message || 'Soumission impossible.', 'error');
      submitButton.disabled = false;
      submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Soumettre ma candidature';
    }
  });
})();
</script>
</body>
</html>

