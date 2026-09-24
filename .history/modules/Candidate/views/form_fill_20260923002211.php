<?php
declare(strict_types=1);
$email = strtolower((string) ($_SESSION['candidate_email'] ?? ''));
$country = strtoupper((string) ($_SESSION['candidate_country'] ?? ''));
$countryOptions = array_values(array_unique(array_map(static fn($value) => trim((string) $value), $candidateCountryOptions ?? [])));
$verified = $email !== '';
$catalog = $candidateFormCatalog ?? [];
$firstFormId = (int) (array_key_first($catalog) ?? 0);
?>
<script>(function(){const token=<?= json_encode(csrf_token(), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;const nativeFetch=window.fetch.bind(window);window.fetch=function(input,init){if(init&&init.body instanceof FormData&&!init.body.has('csrf_token'))init.body.append('csrf_token',token);return nativeFetch(input,init)};})();</script>
<script>(function(){const nativeFetch=window.fetch.bind(window);let elapsedTimer=null;let startedAt=0;let completedElapsedMs=0;const setSubmitting=function(active){const form=document.getElementById('candidateForm');const button=form?.querySelector('button[type="submit"]');if(!button)return;if(active){if(!button.dataset.defaultText)button.dataset.defaultText=button.textContent;startedAt=Date.now();const update=()=>{const elapsed=Math.floor((Date.now()-startedAt)/1000);button.textContent='Envoi en cours... ' + elapsed + ' s'};update();clearInterval(elapsedTimer);elapsedTimer=setInterval(update,1000);button.disabled=true;button.classList.add('is-loading');button.setAttribute('aria-busy','true')}else{completedElapsedMs=Date.now()-startedAt;window.candidateSubmitElapsedMs=completedElapsedMs;clearInterval(elapsedTimer);elapsedTimer=null;button.disabled=false;button.classList.remove('is-loading');button.removeAttribute('aria-busy');button.textContent=button.dataset.defaultText||'Soumettre le dossier'}};window.fetch=async function(input,init){const url=typeof input==='string'?input:(input&&input.url)||'';const isCandidateSubmit=url.includes('/candidate/submit');if(isCandidateSubmit)setSubmitting(true);try{const response=await nativeFetch(input,init);if(isCandidateSubmit)setSubmitting(false);return response}catch(error){if(isCandidateSubmit)setSubmitting(false);throw error}}})();</script>
<script>document.addEventListener('DOMContentLoaded',function(){const confirmation=document.getElementById('confirmation'),success=confirmation?.querySelector('.success');if(success&&!success.querySelector('[data-home-link]')){const home=document.createElement('a');home.className='btn btn-primary';home.href='<?= BASE_URL ?>/';home.dataset.homeLink='true';home.style.marginTop='16px';home.textContent='Retour à l’accueil';success.appendChild(home)}});</script>
<script>document.addEventListener('DOMContentLoaded',function(){const markRequired=()=>document.querySelectorAll('input[required],select[required],textarea[required]').forEach(function(control){const label=control.id?document.querySelector('label[for="'+CSS.escape(control.id)+'"]'):control.closest('.field')?.querySelector('label');if(label)label.classList.add('required-label')});markRequired();new MutationObserver(markRequired).observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['required']})});</script>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Candidature | Criteval Pro</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
  <style>
body{background:#f5f7fb}.candidate-wizard{max-width:980px;margin:35px auto;padding:0 18px}.candidate-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:26px;box-shadow:0 8px 30px #1018280d;margin-bottom:18px}.candidate-card h1{margin-top:0}.steps{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 18px}.step{padding:8px 13px;border-radius:999px;background:#eef2f7;color:#64748b;font-size:13px;transition:background .2s,color .2s}.step.active,.step.completed{background:#0f766e;color:#fff}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.field{display:flex;flex-direction:column;gap:7px}.field.full{grid-column:1/-1}.field label{font-weight:600;color:#344054}.required-label::after{content:' *';color:#dc2626;font-weight:700}.field-invalid{border-color:#dc2626!important;box-shadow:0 0 0 3px rgba(220,38,38,.16)!important;background:#fff7f7!important}.field input,.field select,.field textarea{border:1px solid #d0d5dd;border-radius:8px;padding:11px;font:inherit}.candidate-card select{background:#fff;color:#1e293b}.candidate-card select option,.candidate-card select option:checked{background:#fff;color:#1e293b}.field textarea{min-height:110px;resize:vertical}.criteria{border:1px solid #e4e7ec;border-radius:10px;margin:12px 0;padding:15px}.criteria-head{display:flex;gap:10px;align-items:flex-start}.criteria-head input{margin-top:5px}.criteria-proof{display:none;margin:14px 0 0 25px;padding:14px;background:#f8fafc;border-radius:8px}.criteria-proof.open{display:block}.muted{color:#667085}.notice{padding:12px;border-radius:8px;background:#fffaeb;color:#92400e;margin:12px 0}.actions{display:flex;justify-content:space-between;gap:10px;margin-top:20px}.btn{cursor:pointer}.btn.is-loading{opacity:.8;cursor:wait}.btn.is-loading::before{content:'';width:13px;height:13px;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;animation:submit-spin .7s linear infinite}@keyframes submit-spin{to{transform:rotate(360deg)}}.hidden{display:none!important}.success{background:#ecfdf3;color:#067647;padding:18px;border-radius:10px}.organisation-summary{background:#f8fafc;border-radius:10px;padding:14px;margin:12px 0}.small{font-size:13px}@media(max-width:700px){.grid{grid-template-columns:1fr}.field.full{grid-column:auto}}
</style><style>.success{position:relative;min-height:150px;padding-right:245px}.success p{color:#067647}.success #confirmationElapsed{position:absolute;top:50%;right:24px;display:flex;align-items:center;gap:10px;width:190px;margin:0;transform:translateY(-50%);font-size:16px;font-weight:700;line-height:1.3}.success #confirmationElapsed::before{content:'⏱';font-size:52px;line-height:1;color:#0f766e}.success #confirmationElapsed::after{content:'';position:absolute;inset:-14px -10px;border:1px solid #b7ead2;border-radius:12px;pointer-events:none}.success #confirmationElapsed{z-index:1}@media (max-width:640px){.success{min-height:0;padding-right:18px}.success #confirmationElapsed{position:relative;top:auto;right:auto;width:auto;margin:18px 0 0;transform:none}.success #confirmationElapsed::after{inset:-8px -10px}}
</style>
  <style>
    :root {
      --primary: #1A3C5E;
      --primary-light: #2558891a;
      --secondary: #2EAF7D;
      --secondary-light: #2eaf7d1a;
      --accent: #F5A623;
      --danger: #E74C3C;
      --neutral-light: #F4F6FA;
      --text-main: #2C3E50;
      --text-muted: #7F8C8D;
      --border: #DDE1E7;
      --shadow-md: 0 4px 24px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
    }
    * { box-sizing: border-box; }
    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
      min-height: 100%;
      height: 100%;
      overflow-x: hidden;
      overflow-y: auto;
      overscroll-behavior: none;
    }
    body {
      display: block;
      font-family: 'Inter', sans-serif;
      color: var(--text-main);
      background: var(--neutral-light);
    }
    .pub-nav {
      position: sticky;
      top: 0;
      z-index: 40;
      height: 70px;
      display: flex;
      align-items: center;
      gap: 20px;
      padding: 0 40px;
      background: rgba(255,255,255,0.96);
      border-bottom: 1px solid var(--border);
      backdrop-filter: blur(12px);
    }
    .nav-logo { display: flex; align-items: center; gap: 10px; }
    .nav-logo .logo-icon {
      width: 38px; height: 38px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--primary);
      color: #fff;
      font-size: 18px;
    }
    .nav-logo span {
      font-family: 'Poppins', sans-serif;
      font-size: 20px;
      font-weight: 700;
      color: var(--primary);
    }
    .nav-logo span em { color: var(--secondary); font-style: normal; }
    .nav-links {
      display: flex;
      align-items: center;
      gap: 28px;
      list-style: none;
      margin: 0;
      padding: 0;
      margin-left: auto;
    }
    .nav-links a {
      color: var(--text-muted);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    .nav-links a:hover { color: var(--primary); }
    .nav-links .nav-action {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px 14px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: transparent;
      color: var(--primary);
      font-weight: 500;
      line-height: 1;
      transition: all 0.2s ease;
    }
    .nav-links .nav-action:hover {
      background: var(--primary);
      color: #fff;
      border-color: var(--primary);
      text-decoration: none;
    }
    .nav-links .nav-action.btn-secondary {
      background: var(--secondary);
      border-color: var(--secondary);
      color: #fff;
      box-shadow: 0 4px 16px rgba(46,175,125,0.35);
    }
    .nav-links .nav-action.btn-secondary:hover {
      background: #269a6d;
      border-color: #269a6d;
      color: #fff;
    }
    .candidate-hero {
      position: fixed;
      inset: 70px 0 0 0;
      height: calc(100vh - 70px);
      min-height: calc(100vh - 70px);
      max-height: calc(100vh - 70px);
      background: linear-gradient(135deg, #0d2540 0%, #1A3C5E 45%, #1a4a4a 100%);
      overflow-x: hidden;
      overflow-y: auto;
      padding: 18px 24px 10px;
    }
    .candidate-hero::before {
      content: "";
      position: absolute;
      inset: 0;
      opacity: 0.05;
      background-image: radial-gradient(circle at 25% 25%, #fff 1px, transparent 1px), radial-gradient(circle at 75% 75%, #fff 1px, transparent 1px);
      background-size: 52px 52px;
    }
    .candidate-shell {
      max-width: 1080px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
      display: grid;
      gap: 18px;
    }
    .candidate-card {
      background: rgba(255,255,255,0.96);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 18px;
      box-shadow: var(--shadow-md);
      padding: 28px;
      position: relative;
      overflow: hidden;
      height: auto;
      min-height: 0;
    }
    .candidate-card::before {
      content: "";
      position: absolute;
      inset: 0 auto auto 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, var(--secondary), rgba(255,255,255,0.25));
    }
    .candidate-card:first-child {
      background: rgba(15, 27, 43, 0.38);
      border-color: rgba(255,255,255,0.10);
      box-shadow: 0 18px 42px rgba(0,0,0,0.16);
      min-height: 0;
      height: auto;
    }
    .candidate-card:first-child::before { background: linear-gradient(90deg, var(--secondary), #7ce0b7); }
    .candidate-card h1, .candidate-card h2, .candidate-card h3 {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      color: var(--primary);
      line-height: 1.2;
    }
    .candidate-card:first-child h1,
    .candidate-card:first-child .muted,
    .candidate-card:first-child .candidate-tag {
      color: #fff;
    }
    .candidate-card h1 { font-size: clamp(28px, 2vw, 40px); }
    .candidate-card h2 { font-size: clamp(22px, 1.8vw, 30px); }
    .muted {
      color: var(--text-muted);
      margin: 0;
      line-height: 1.6;
    }
    .candidate-meta-row {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 18px;
    }
    .candidate-tag {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,0.12);
      color: rgba(255,255,255,0.8);
      border: 1px solid rgba(255,255,255,0.16);
      font-size: 12px;
      font-weight: 600;
    }
    .candidate-tag.is-active {
      background: linear-gradient(135deg, var(--secondary), #2AAE7A);
      border-color: rgba(46,175,125,0.9);
      color: #fff;
      box-shadow: 0 8px 18px rgba(46,175,125,0.25);
    }
    .candidate-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 18px;
    }
    .field {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .field.full { grid-column: 1 / -1; }
    .field label { font-size: 14px; font-weight: 600; color: var(--text-main); }
    .required-label::after { content: ' *'; color: var(--danger); }
    .field input,
    .field select,
    .field textarea {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid rgba(26,60,94,0.15);
      border-radius: 10px;
      background: #fff;
      color: var(--text-main);
      font: inherit;
    }
    .field input:focus,
    .field select:focus,
    .field textarea:focus {
      outline: none;
      border-color: rgba(46,175,125,0.8);
      box-shadow: 0 0 0 4px rgba(46,175,125,0.12);
    }
    .country-field {
      position: relative;
    }
    .country-input-wrap {
      position: relative;
      width: 100%;
    }
    .country-options {
      position: absolute;
      left: 0;
      right: 0;
      top: calc(100% + 8px);
      z-index: 30;
      display: none;
      max-height: 220px;
      overflow-y: auto;
      padding: 6px;
      background: rgba(255,255,255,0.98);
      border: 1px solid rgba(26,60,94,0.12);
      border-radius: 12px;
      box-shadow: 0 20px 50px rgba(15,23,42,0.18);
    }
    .country-options.is-open {
      display: block;
    }
    .country-option {
      width: 100%;
      border: 0;
      background: transparent;
      color: var(--text-main);
      text-align: left;
      padding: 10px 12px;
      border-radius: 8px;
      cursor: pointer;
      font: inherit;
      font-size: 14px;
    }
    .country-option:hover,
    .country-option:focus-visible {
      background: rgba(46,175,125,0.08);
      color: var(--primary);
      outline: none;
    }
    .field textarea { min-height: 110px; resize: vertical; }
    .field-invalid {
      border-color: var(--danger) !important;
      box-shadow: 0 0 0 4px rgba(231,76,60,0.12) !important;
      background: #fff7f7 !important;
    }
    .notice {
      padding: 12px 14px;
      border-radius: 10px;
      background: rgba(245,166,35,0.12);
      border: 1px solid rgba(245,166,35,0.25);
      color: #7a4a00;
      margin: 0;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
      white-space: nowrap;
    }
    .btn-sm {
      padding: 6px 14px;
      font-size: 13px;
    }
    .btn-primary {
      background: var(--primary);
      color: #fff;
      box-shadow: 0 10px 22px rgba(26,60,94,0.20);
    }
    .btn-primary:hover {
      background: #15304d;
      transform: translateY(-1px);
    }
    .btn-secondary {
      background: var(--secondary);
      color: #fff;
      border: 1px solid transparent;
      box-shadow: 0 4px 16px rgba(46,175,125,0.35);
    }
    .btn-secondary:hover {
      background: #269a6d;
      transform: translateY(-1px);
    }
    .btn-ghost {
      background: transparent;
      color: var(--primary);
      border: 1.5px solid var(--border);
    }
    .btn-ghost:hover {
      background: var(--primary);
      color: #fff;
      border-color: var(--primary);
    }
    .btn.is-loading { opacity: 0.85; cursor: wait; }
    .btn.is-loading::before {
      content: "";
      width: 13px;
      height: 13px;
      border: 2px solid rgba(255,255,255,0.9);
      border-right-color: transparent;
      border-radius: 50%;
      margin-right: 8px;
      animation: submit-spin 0.7s linear infinite;
    }
    @keyframes submit-spin { to { transform: rotate(360deg); } }
    .criteria {
      border: 1px solid rgba(26,60,94,0.1);
      border-radius: 12px;
      background: #f9fbfd;
      padding: 16px;
      margin-top: 12px;
    }
    .criteria-head {
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }
    .criteria-proof {
      display: none;
      margin-top: 12px;
      padding: 12px;
      background: #f8fafc;
      border-radius: 10px;
      border: 1px solid rgba(26,60,94,0.08);
    }
    .criteria-proof.open { display: block; }
    .organisation-summary {
      padding: 14px 16px;
      background: #f8fafc;
      border: 1px solid rgba(26,60,94,0.08);
      border-radius: 12px;
      color: var(--text-main);
      margin-bottom: 18px;
    }
    .small { font-size: 12px; }
    .actions {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      margin-top: 20px;
    }
    .success {
      padding: 18px;
      border-radius: 12px;
      background: #ecfdf5;
      border: 1px solid rgba(46,175,125,0.25);
      color: #065f46;
      position: relative;
    }
    .success p { color: #065f46; }
    @media (max-width: 700px) {
      .pub-nav { padding: 0 18px; }
      .nav-links { display: none; }
      .candidate-hero { padding: 34px 14px 52px; }
      .candidate-grid { grid-template-columns: 1fr; }
      .candidate-card { padding: 22px 18px; }
      .actions { flex-direction: column; align-items: stretch; }
      .btn { width: 100%; }
    }
  </style>
</head>
<body>
  <header class="pub-nav">
    <div class="nav-logo">
      <div class="logo-icon"><i class="fa fa-shield-halved"></i></div>
      <span>Criteval <em>Pro</em></span>
    </div>
    <ul class="nav-links">
      <li><a class="nav-action" href="<?= BASE_URL ?>">Accueil</a></li>
      <li><a class="nav-action btn-secondary" href="<?= BASE_URL ?>/candidate">Candidature</a></li>
      <li><a class="nav-action" href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
    </ul>
  </header>

  <main class="candidate-hero">
    <div class="candidate-shell">
      <section class="candidate-card">
        <h1>Formulaire d’évaluation organisationnelle</h1>
        <p class="muted" style="margin-top: 8px;">Un parcours sécurisé en quatre étapes. Vos réponses sont enregistrées uniquement après validation finale.</p>
        <div class="candidate-meta-row" style="margin-top: 16px;">
          <span class="candidate-tag is-active">1. Accès</span>
          <span class="candidate-tag">2. Organisation</span>
          <span class="candidate-tag">3. Preuves</span>
          <span class="candidate-tag">4. Confirmation</span>
        </div>
      </section>

      <section id="accessStep" class="candidate-card <?= $verified ? 'hidden' : '' ?>">
      <h2>Vérifier votre accès</h2>
      <p class="muted" style="margin-top: 8px;">Utilisez votre adresse professionnelle. Elle doit appartenir à un domaine enregistré pour une organisation.</p>
      <form id="requestOtpForm" style="margin-top: 22px;">
        <div class="candidate-grid">
          <div class="field">
            <label for="accessEmail" class="required-label">Email professionnel</label>
            <input id="accessEmail" name="email" type="email" autocomplete="email" required placeholder="vous@organisation.org">
          </div>
          <div class="field country-field">
            <label for="accessCountry" class="required-label">Pays</label>
            <div class="country-input-wrap">
              <input id="accessCountry" name="country_code" autocomplete="off" required maxlength="25" placeholder="Saisir un pays" aria-expanded="false" aria-controls="accessCountryOptions">
              <div id="accessCountryOptions" class="country-options" role="listbox" aria-label="Options de pays">
                <?php foreach ($countryOptions as $option): ?>
                  <button type="button" class="country-option" data-value="<?= e((string) $option) ?>" role="option"><?= e((string) $option) ?></button>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
        <div style="margin-top: 18px;">
          <button class="btn btn-primary" type="submit">Recevoir le code</button>
        </div>
      </form>

      <form id="verifyOtpForm" class="hidden" style="margin-top: 18px;">
        <input type="hidden" name="email">
        <input type="hidden" name="form_id" value="0">
        <div class="field">
          <label for="otp" class="required-label">Code reçu par email</label>
          <input id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
        </div>
        <button class="btn btn-primary" type="submit">Vérifier et continuer</button>
      </form>

      <p id="accessMessage" class="notice hidden" role="alert" style="margin-top: 18px;"></p>
    </section>
<?php if ($verified): ?><form id="candidateForm" enctype="multipart/form-data"><section class="wizard-panel candidate-card" data-panel="2"><h2>Organisation et session</h2><p class="muted">Les informations connues sont préremplies. Choisissez l’évaluation ou la formation encore ouverte.</p><?php if ($candidateOrganization): ?><div class="organisation-summary"><strong><?= e((string) ($candidateOrganization['organization'] ?: $candidateOrganization['title'])) ?></strong><br><span class="small muted">Zone : <?= e((string) ($candidateOrganization['intervention_zone'] ?? 'Non renseignée')) ?> · Pays : <?= e((string) ($candidateOrganization['country_code'] ?? $country)) ?></span></div><?php endif; ?><div class="grid"><div class="field"><label for="representator">Nom du représentant</label><input id="representator" name="candidate_name" required maxlength="150"></div><div class="field"><label>Email vérifié</label><input value="<?= e($email) ?>" disabled></div><div class="field"><label for="formId">Évaluation / formation</label><select id="formId" name="form_id" required><option value="">Sélectionner</option><?php foreach ($catalog as $id => $entry): ?><option value="<?= (int) $id ?>" <?= $id === $firstFormId ? 'selected' : '' ?>><?= e((string) ($entry['form']['title'] ?? 'Formulaire')) ?></option><?php endforeach; ?></select></div><div class="field"><label for="sessionId">Session du calendrier</label><select id="sessionId" name="training_session_id" required><option value="">Sélectionner une session</option></select></div><div class="field country-field"><label for="country">Pays</label><div class="country-input-wrap"><input id="country" name="country_code" value="<?= e($country) ?>" autocomplete="off" required maxlength="25" aria-expanded="false" aria-controls="countryOptions"><div id="countryOptions" class="country-options" role="listbox" aria-label="Options de pays"><?php foreach ($countryOptions as $option): ?><button type="button" class="country-option" data-value="<?= e((string) $option) ?>" role="option"><?= e((string) $option) ?></button><?php endforeach; ?></div></div></div><div class="field"><label>Organisation</label><input name="organisation" value="<?= e((string) ($candidateOrganization['organization'] ?? $candidateOrganization['title'] ?? '')) ?>" readonly></div></div><div id="customFields" class="grid" style="margin-top:16px"></div><div class="actions"><span></span><button type="button" class="btn btn-primary nextBtn">Continuer vers les critères</button></div></section><section class="wizard-panel candidate-card hidden" data-panel="3"><h2>Critères et éléments de preuve</h2><p class="muted">Cochez uniquement les critères réalisés. Chaque critère coché doit comporter une explication et au moins un justificatif.</p><div id="criteriaList"></div><div class="actions"><button type="button" class="btn btn-secondary prevBtn">Retour</button><button type="submit" class="btn btn-primary">Soumettre le dossier</button></div></section></form><section id="confirmation" class="candidate-card hidden"><div class="success"><h2>Dossier reçu</h2><p id="confirmationText">Votre dossier a été enregistré et une copie a été envoyée à votre adresse email.</p></div></section><?php endif; ?></main>
<script>
document.addEventListener('DOMContentLoaded', function () {
	const initCountryMenu = (inputId, menuId) => {
		const input = document.getElementById(inputId);
		const menu = document.getElementById(menuId);
		if (!input || !menu) return;
		const options = Array.from(menu.querySelectorAll('.country-option'));
		const closeMenu = () => {
			menu.classList.remove('is-open');
			input.setAttribute('aria-expanded', 'false');
		};
		const syncMenu = () => {
			const query = input.value.trim().toLowerCase();
			let visibleCount = 0;
			options.forEach(option => {
				const value = option.dataset.value || option.textContent || '';
				const match = !query || value.toLowerCase().includes(query);
				option.hidden = !match;
				if (match) visibleCount++;
			});
			const shouldOpen = visibleCount > 0 && document.activeElement === input;
			menu.classList.toggle('is-open', shouldOpen);
			input.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
		};
		const selectOption = (option) => {
			const nextValue = option.dataset.value || option.textContent || '';
			input.value = nextValue;
			closeMenu();
			input.dispatchEvent(new Event('input', { bubbles: true }));
			input.focus();
		};
		input.addEventListener('focus', () => {
			syncMenu();
		});
		input.addEventListener('click', () => {
			syncMenu();
		});
		input.addEventListener('input', syncMenu);
		options.forEach(option => {
			option.addEventListener('mousedown', event => {
				event.preventDefault();
				selectOption(option);
			});
			option.addEventListener('click', event => {
				event.preventDefault();
				selectOption(option);
			});
		});
		document.addEventListener('click', (event) => {
			if (!input.closest('.country-input-wrap')?.contains(event.target)) closeMenu();
		});
		input.addEventListener('blur', () => {
			setTimeout(closeMenu, 120);
		});
		input.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') closeMenu();
			if (event.key === 'ArrowDown' && !menu.classList.contains('is-open')) {
				syncMenu();
			}
		});
	};
	initCountryMenu('accessCountry', 'accessCountryOptions');
	initCountryMenu('country', 'countryOptions');
	const steps = document.querySelectorAll('.steps .step');
	const updateStep = (current) => steps.forEach((step, index) => {
		step.classList.toggle('active', index === current - 1);
		step.classList.toggle('completed', index < current - 1);
	});
		const candidateForm = document.getElementById('candidateForm');
		const showInvalidField = (field) => {
			document.querySelectorAll('.field-invalid').forEach((item) => item.classList.remove('field-invalid'));
			if (!field) return;
			field.classList.add('field-invalid');
			field.scrollIntoView({ behavior: 'smooth', block: 'center' });
			field.focus({ preventScroll: true });
		};
	updateStep(candidateForm ? 2 : 1);
	document.querySelector('.nextBtn')?.addEventListener('click', () => {
		const panel = document.querySelector('[data-panel="2"]');
			const invalidField = panel?.querySelector('input:invalid,select:invalid,textarea:invalid');
			if (invalidField) {
				showInvalidField(invalidField);
				return;
			}
			showInvalidField(null);
			updateStep(3);
	});
	document.querySelector('.prevBtn')?.addEventListener('click', () => updateStep(2));
	candidateForm?.addEventListener('submit', () => {
		if (candidateForm.checkValidity()) updateStep(4);
	});

	const formSelect = document.getElementById('formId');
	const sessionSelect = document.getElementById('sessionId');
	const criteriaList = document.getElementById('criteriaList');
	const catalog = <?= json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
	if (!formSelect || !sessionSelect || !criteriaList) return;
	const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));
	const renderCriteria = () => {
		const entry = catalog[formSelect.value] || { criteria: [], sessions: [] };
		const session = (entry.sessions || []).find((item) => String(item.id) === sessionSelect.value);
		const criteria = session?.criteria?.length ? session.criteria : (entry.criteria || []);
		criteriaList.innerHTML = criteria.length ? criteria.map((criterion) => {
			const id = Number(criterion.id);
					return '<div class="criteria"><div class="criteria-head"><input type="checkbox" name="criteria_checked[]" value="' + id + '" id="criterion-' + id + '"><label for="criterion-' + id + '"><strong>' + escapeHtml(criterion.label) + '</strong><br><span class="small muted">' + escapeHtml(criterion.description || '') + '</span></label></div><div class="criteria-proof"><div class="field"><label>Détails et justification</label><textarea name="criteria[' + id + '][details]" placeholder="Décrivez les actions réalisées…"></textarea><label>Pièces justificatives (optionnel)</label><input type="file" name="evidence[' + id + '][]" multiple accept="application/pdf,image/jpeg,image/png,image/webp"></div></div></div>';
		}).join('') : '<p class="muted">Aucun critère n’est configuré pour cette évaluation ou cette session.</p>';
		criteriaList.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => checkbox.addEventListener('change', () => {
			const criterion = checkbox.closest('.criteria');
			criterion.querySelector('.criteria-proof').classList.toggle('open', checkbox.checked);
			criterion.querySelector('textarea').required = checkbox.checked;
		}));
	};
	sessionSelect.addEventListener('change', renderCriteria);
	formSelect.addEventListener('change', () => setTimeout(renderCriteria, 0));
	renderCriteria();
});
</script>
<script>(function(){const base='<?= BASE_URL ?>',verified=<?= $verified?'true':'false' ?>,catalog=<?= json_encode($catalog,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;const msg=document.getElementById('accessMessage');const showMsg=t=>{if(msg){msg.textContent=t;msg.classList.remove('hidden')}};const request=document.getElementById('requestOtpForm'),verify=document.getElementById('verifyOtpForm');if(request)request.addEventListener('submit',async e=>{e.preventDefault();showMsg('Envoi du code…');const r=await fetch(base+'/otp',{method:'POST',body:new FormData(request)}),j=await r.json();if(!r.ok||!j.success){showMsg(j.message||'Veuillez saisir un email professionnel valide.');return}verify.email.value=request.email.value;verify.classList.remove('hidden');showMsg('Code envoyé. Vérifiez votre boîte mail.');document.getElementById('otp').focus()});if(verify)verify.addEventListener('submit',async e=>{e.preventDefault();showMsg('Vérification…');const r=await fetch(base+'/otp/verify',{method:'POST',body:new FormData(verify)}),j=await r.json();if(!r.ok||!j.success){showMsg(j.message||'Code invalide ou expiré.');return}window.location.reload()});if(!verified)return;const fs=document.getElementById('formId'),ss=document.getElementById('sessionId'),cl=document.getElementById('criteriaList'),custom=document.getElementById('customFields');const esc=v=>String(v).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));const fname=v=>String(v||'field').replace(/[^A-Za-z0-9_-]/g,'_');function render(){const en=catalog[fs.value]||{criteria:[],sessions:[],form:{}};ss.innerHTML='<option value="">Sélectionner une session</option>';(en.sessions||[]).forEach(s=>{ss.innerHTML+='<option value="'+s.id+'">'+esc(s.name)+' — '+esc(s.session_date||'date à confirmer')+' ('+esc(s.format||'session')+')</option>'});cl.innerHTML='';(en.criteria||[]).forEach(c=>{const d=document.createElement('div');d.className='criteria';d.innerHTML='<div class="criteria-head"><input type="checkbox" name="criteria_checked[]" value="'+c.id+'" id="criterion-'+c.id+'"><label for="criterion-'+c.id+'"><strong>'+esc(c.label)+'</strong><br><span class="small muted">'+esc(c.description||'')+'</span></label></div><div class="criteria-proof"><div class="field"><label>Détails et justification</label><textarea name="criteria['+c.id+'][details]" '+(c.is_required?'required':'')+' placeholder="Décrivez les actions réalisées…"></textarea><label>Pièces justificatives</label><input type="file" name="evidence['+c.id+'][]" multiple accept="application/pdf,image/jpeg,image/png,image/webp" '+(c.is_required?'required':'')+'></div></div>';const cb=d.querySelector('input');cb.addEventListener('change',()=>d.querySelector('.criteria-proof').classList.toggle('open',cb.checked));cl.appendChild(d)});custom.innerHTML='';let layout=[];try{layout=JSON.parse(en.form.layout_json||'[]')}catch(e){}if(layout&&layout.fields)layout=layout.fields;if(Array.isArray(layout))layout.forEach((x,i)=>{if(['heading','paragraph','divider','section','file'].includes(x.type))return;const n=fname(x.name||'field_'+i),d=document.createElement('div');d.className='field';d.innerHTML='<label>'+esc(x.label||n)+'</label>'+(x.type==='textarea'?'<textarea name="custom_fields['+n+']"></textarea>':'<input type="'+(['email','number','date'].includes(x.type)?x.type:'text')+'" name="custom_fields['+n+']" '+(x.required?'required':'')+'>');custom.appendChild(d)})}fs.addEventListener('change',render);render();document.querySelector('.nextBtn')?.addEventListener('click',()=>{const p=document.querySelector('[data-panel="2"]');if(!p.querySelector('input:invalid,select:invalid,textarea:invalid')){p.classList.add('hidden');document.querySelector('[data-panel="3"]').classList.remove('hidden')}});document.querySelector('.prevBtn')?.addEventListener('click',()=>{document.querySelector('[data-panel="3"]').classList.add('hidden');document.querySelector('[data-panel="2"]').classList.remove('hidden')});document.getElementById('candidateForm')?.addEventListener('submit',async e=>{e.preventDefault();if(!e.target.reportValidity())return;const b=e.target.querySelector('button[type=submit]');b.disabled=true;const r=await fetch(base+'/candidate/submit',{method:'POST',body:new FormData(e.target)}),j=await r.json();if(!r.ok||!j.success){alert(j.message||'Soumission impossible.');b.disabled=false;return}e.target.classList.add('hidden');document.getElementById('confirmation').classList.remove('hidden');document.getElementById('confirmationText').textContent=j.message||'Votre dossier a été reçu et une confirmation a été envoyée par email.'})})();</script></body></html>
</script><script>document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('#criteriaList textarea[name^="criteria["]').forEach(function(textarea){textarea.required=false});document.querySelectorAll('#criteriaList input[type="checkbox"]').forEach(function(checkbox){checkbox.addEventListener('change',function(){var textarea=checkbox.closest('.criteria')?.querySelector('textarea[name^="criteria["]');if(textarea)textarea.required=checkbox.checked})})});</script><script>document.addEventListener('DOMContentLoaded',function(){const confirmation=document.getElementById('confirmation'),confirmationText=document.getElementById('confirmationText');if(!confirmation||!confirmationText)return;const showElapsed=()=>{if(confirmation.classList.contains('hidden')||document.getElementById('confirmationElapsed'))return;const elapsed=document.createElement('p');elapsed.id='confirmationElapsed';elapsed.className='muted';elapsed.textContent='Temps d’envoi : '+((window.candidateSubmitElapsedMs||0)/1000).toFixed(1).replace('.',',')+' s';confirmationText.insertAdjacentElement('afterend',elapsed)};new MutationObserver(showElapsed).observe(confirmation,{attributes:true,attributeFilter:['class']});showElapsed()});</script>
        </div>
      </main>
    </div>
  </div>
</body></html>
