<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion - Criteval_pro</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body>

<div id="page-login" class="page active">
  <div class="auth-bg-orbs"><div class="orb orb1"></div><div class="orb orb2"></div></div>
  <div class="auth-card animate-fadeInUp">
    <div class="auth-logo">
      <div class="logo-big"><i class="fas fa-chart-line"></i></div>
      <h1>Criteval<em style="color:var(--secondary);font-style:normal">_pro</em></h1>
      <p>Connexion a votre espace administration</p>
    </div>

    <?php if (!empty($error)): ?>
      <div style="margin-bottom:18px;padding:12px 14px;background:rgba(231,76,60,0.12);border:1px solid rgba(231,76,60,0.28);border-radius:10px;color:#fff;font-size:13px">
        <i class="fas fa-triangle-exclamation" style="color:var(--danger)"></i> <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/login">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="form-group">
        <label class="form-label">Nom d'utilisateur</label>
        <div class="form-input-group">
          <i class="fas fa-user input-icon"></i>
          <input type="text" name="credential" class="form-input" placeholder="admin" value="<?= e($credential ?? 'admin') ?>" required autocomplete="username">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Mot de passe</label>
        <div class="form-input-group">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" name="password" class="form-input" placeholder="Mot de passe" required autocomplete="current-password">
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
        <label style="display:flex;align-items:center;gap:8px;color:rgba(255,255,255,0.5);font-size:13px;cursor:pointer">
          <input type="checkbox" checked style="accent-color:var(--secondary)"> Se souvenir de moi
        </label>
        <a href="<?= BASE_URL ?>/forgot-password" style="color:var(--secondary);font-size:13px;text-decoration:none">Mot de passe oublie ?</a>
      </div>
      <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;padding:14px">
        <i class="fas fa-sign-in-alt"></i> Se connecter
      </button>
    </form>

    <div style="text-align:center;margin-top:20px">
      <a href="<?= BASE_URL ?>/" style="color:rgba(255,255,255,0.35);font-size:13px;text-decoration:none"><i class="fas fa-arrow-left"></i> Retour au site</a>
    </div>
    <div style="margin-top:24px;padding:14px;background:rgba(46,175,125,0.1);border-radius:10px;border:1px solid rgba(46,175,125,0.2)">
      <p style="color:rgba(255,255,255,0.5);font-size:12px;text-align:center"><i class="fas fa-info-circle" style="color:var(--secondary)"></i> Utilisez les identifiants fournis par votre administrateur.</p>
    </div>
  </div>
</div>

</body>
</html>
