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
    const data = new FormData();
    data.append('email', email);
    data.append('form_id', document.getElementById('candidateFormId')?.value || '1');
    data.append('candidate_name', document.querySelector('[name="candidate_name"]')?.value || '');
    data.append('country_code', document.querySelector('[name="country_code"]')?.value || '');
    document.querySelectorAll('[name^="evidence["]').forEach((input) => {
      if (input.files[0]) data.append(input.name, input.files[0]);
    });

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

