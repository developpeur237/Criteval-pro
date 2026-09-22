<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Installation de Criteval Pro</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body class="install-page">
<div id="page-install" class="page active">
  <div class="auth-bg-orbs"><div class="orb orb1"></div><div class="orb orb2"></div></div>
  <div class="install-card animate-fadeInUp">
    <div class="install-header">
      <div class="logo-big"><i class="fas fa-cogs"></i></div>
      <h1>Installateur <strong>Criteval_pro</strong></h1>
      <p>La version MVP utilise une base SQLite locale. Aucune configuration MySQL n’est nécessaire.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="install-note" style="background:rgba(231,76,60,0.12);border-color:rgba(231,76,60,0.28);color:#fff;">
        <i class="fas fa-triangle-exclamation" style="color:var(--danger)"></i> <?= e($error) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="install-note" style="background:rgba(46,175,125,0.12);border-color:rgba(46,175,125,0.28);color:#fff;">
        <i class="fas fa-check-circle" style="color:var(--success)"></i> <?= e($success) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/install">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="install-note">
        <p><strong>Base MVP :</strong> <code>storage/criteval_pro.sqlite</code></p>
        <p>Le fichier est créé automatiquement depuis <code>database/criteval_pro.sqlite.sql</code>. Le schéma MySQL existant est conservé pour la migration future.</p>
      </div>

      <div class="install-actions">
        <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;padding:16px;font-size:15px;">
          <i class="fas fa-database"></i> Initialiser et continuer
        </button>
      </div>
    </form>

    <div class="install-footer">
      <p>SQLite convient au MVP local et ne requiert aucun service de base de données.</p>
      <p><a href="<?= BASE_URL ?>/login">Retour à la connexion</a></p>
    </div>
  </div>
</div>
</body>
</html>
