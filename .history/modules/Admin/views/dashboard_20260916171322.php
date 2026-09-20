<!DOCTYPE html>
<?php
$currentUser = current_user();
$moduleCounts = $moduleCounts ?? [];
$appSettings = app_settings();
$databaseInfo = $databaseInfo ?? null;
$users = $users ?? [];
$availableRoles = function_exists('available_roles') ? available_roles() : ['superadmin', 'admin', 'user', 'visitor'];
$rolePermissionDefaults = [];
foreach ($availableRoles as $role) {
    $rolePermissionDefaults[$role] = default_permissions_for_role($role);
}
$modulePermissionLabels = [
    'apercu' => 'Aperçu',
    'projets' => 'Organisations',
    'criteres' => 'Critères',
    'formulaires' => 'Formulaires',
    'evaluations' => 'Évaluations',
    'classements' => 'Classements',
    'calendrier' => 'Planning',
    'parametres' => 'Paramètres',
    'utilisateurs' => 'Utilisateurs',
];
$overview = $overview ?? [];
$overviewKpis = $overview['kpis'] ?? [];
$overviewLatest = $overview['latest'] ?? [];
$overviewRanking = $overview['ranking'] ?? [];
$overviewUpcoming = $overview['upcoming'] ?? [];
$permissionActionLabels = function_exists('permission_action_labels') ? permission_action_labels() : ['view' => 'Voir', 'create' => 'Créer', 'update' => 'Modifier', 'delete' => 'Supprimer'];
?>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Criteval Pro</title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<!-- External Libraries -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui/1.13.2/themes/base/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/fr.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

<style>
:root {
  --primary: #1A3C5E;
  --primary-light: #2558891a;
  --secondary: #2EAF7D;
  --secondary-light: #2eaf7d1a;
  --accent: #F5A623;
  --accent-light: #f5a6231a;
  --danger: #E74C3C;
  --neutral-dark: #1E1E2E;
  --neutral-dark2: #252535;
  --neutral-dark3: #2d2d42;
  --neutral-light: #F4F6FA;
  --text-main: #2C3E50;
  --text-muted: #7F8C8D;
  --white: #FFFFFF;
  --border: #DDE1E7;
  --border-dark: #3a3a55;
  --sidebar-w: 260px;
  --topbar-h: 64px;
  --radius: 12px;
  --shadow: 0 2px 12px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.05);
  --shadow-md: 0 4px 24px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
  --shadow-lg: 0 8px 40px rgba(0,0,0,0.16);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { font-family: 'Inter', sans-serif; color: var(--text-main); background: var(--neutral-light); overflow-x: hidden; }

/* ══════════════════════════════════════════
   GLOBAL
══════════════════════════════════════════ */
.hidden { display: none !important; }
.flex { display: flex; }
.items-center { align-items: center; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.gap-4 { gap: 16px; }
.font-mono { font-family: 'JetBrains Mono', monospace; }
.text-muted { color: var(--text-muted); }
.fw-600 { font-weight: 600; }
.fw-700 { font-weight: 700; }

.btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 10px 20px; border-radius: 8px; font-family: 'Inter', sans-serif;
  font-size: 14px; font-weight: 500; cursor: pointer; border: none;
  transition: all 0.2s ease; text-decoration: none; white-space: nowrap;
}
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover { background: #15304d; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(26,60,94,0.35); }
.btn-secondary { background: var(--secondary); color: #fff; }
.btn-secondary:hover { background: #269a6d; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(46,175,125,0.35); }
.btn-accent { background: var(--accent); color: #fff; }
.btn-accent:hover { background: #e09415; transform: translateY(-1px); }
.btn-danger { background: var(--danger); color: #fff; }
.btn-danger:hover { background: #c0392b; }
.btn-ghost { background: transparent; color: var(--primary); border: 1.5px solid var(--border); }
.btn-ghost:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
.btn-ghost-dark { background: transparent; color: #aaa; border: 1.5px solid var(--border-dark); }
.btn-ghost-dark:hover { background: var(--secondary); color: #fff; border-color: var(--secondary); }
.btn-sm { padding: 6px 14px; font-size: 13px; }
.btn-lg { padding: 14px 28px; font-size: 16px; border-radius: 10px; }
.btn-icon { padding: 8px; width: 36px; height: 36px; justify-content: center; border-radius: 8px; }

.badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;
}
.badge-success { background: #2eaf7d20; color: #2EAF7D; }
.badge-warning { background: #f5a62320; color: #e09415; }
.badge-danger { background: #e74c3c20; color: #E74C3C; }
.badge-info { background: #1a3c5e20; color: var(--primary); }
.badge-muted { background: #7f8c8d20; color: var(--text-muted); }

/* ══════════════════════════════════════════
   PAGE SYSTEM
══════════════════════════════════════════ */
.page { display: none; }
.page.active { display: block; }

/* ══════════════════════════════════════════
   PUBLIC — LANDING PAGE
══════════════════════════════════════════ */
#page-landing { background: var(--neutral-light); }

/* Navbar */
.pub-nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
  height: 70px; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; padding: 0 40px;
  justify-content: space-between;
  transition: all 0.3s;
}
.pub-nav.scrolled { box-shadow: 0 4px 30px rgba(0,0,0,0.08); }
.nav-logo { display: flex; align-items: center; gap: 10px; }
.nav-logo .logo-icon {
  width: 38px; height: 38px; background: var(--primary); border-radius: 10px;
  display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px;
}
.nav-logo span { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 20px; color: var(--primary); }
.nav-logo span em { color: var(--secondary); font-style: normal; }
.nav-links { display: flex; gap: 32px; list-style: none; }
.nav-links a { text-decoration: none; color: var(--text-muted); font-size: 14px; font-weight: 500; transition: color 0.2s; }
.nav-links a:hover { color: var(--primary); }

/* Hero */
.pub-hero {
  min-height: 100vh; padding-top: 70px;
  background: linear-gradient(135deg, #0d2540 0%, #1A3C5E 45%, #1a4a4a 100%);
  display: flex; align-items: center; position: relative; overflow: hidden;
}
.hero-bg-pattern {
  position: absolute; inset: 0; opacity: 0.04;
  background-image: radial-gradient(circle at 25% 25%, #fff 1px, transparent 1px),
                    radial-gradient(circle at 75% 75%, #fff 1px, transparent 1px);
  background-size: 50px 50px;
}
.hero-glow {
  position: absolute; width: 600px; height: 600px; border-radius: 50%;
  background: radial-gradient(circle, rgba(46,175,125,0.15) 0%, transparent 70%);
  top: -100px; right: -100px; pointer-events: none;
}
.hero-content { max-width: 1200px; margin: 0 auto; padding: 80px 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
.hero-eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(46,175,125,0.2); border: 1px solid rgba(46,175,125,0.4);
  color: #5dd5a8; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 500;
  margin-bottom: 24px;
}
.hero-title { font-family: 'Poppins', sans-serif; font-size: 52px; font-weight: 800; line-height: 1.1; color: #fff; margin-bottom: 20px; }
.hero-title .accent { color: var(--secondary); }
.hero-sub { color: rgba(255,255,255,0.7); font-size: 18px; line-height: 1.6; margin-bottom: 36px; }
.hero-cta { display: flex; gap: 16px; flex-wrap: wrap; }
.hero-stats { display: flex; gap: 32px; margin-top: 48px; padding-top: 32px; border-top: 1px solid rgba(255,255,255,0.1); }
.hero-stat-num { font-family: 'Poppins', sans-serif; font-size: 32px; font-weight: 700; color: var(--secondary); }
.hero-stat-label { color: rgba(255,255,255,0.5); font-size: 13px; margin-top: 2px; }

/* Dashboard preview */
.hero-visual {
  position: relative;
}
.dashboard-preview {
  background: var(--neutral-dark); border-radius: 16px; overflow: hidden;
  box-shadow: 0 40px 80px rgba(0,0,0,0.5); border: 1px solid #3a3a55;
  transform: perspective(1000px) rotateY(-8deg) rotateX(3deg);
  transition: transform 0.5s ease;
}
.dashboard-preview:hover { transform: perspective(1000px) rotateY(-4deg) rotateX(1deg); }
.dp-header {
  background: #252535; padding: 12px 16px; display: flex; align-items: center; gap: 8px;
  border-bottom: 1px solid #3a3a55;
}
.dp-dots { display: flex; gap: 6px; }
.dp-dot { width: 12px; height: 12px; border-radius: 50%; }
.dp-kpis { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; padding: 12px; }
.dp-kpi { background: #252535; border-radius: 8px; padding: 10px; }
.dp-kpi-val { font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 700; color: #fff; }
.dp-kpi-label { font-size: 10px; color: #888; margin-top: 2px; }
.dp-chart-area { padding: 12px; }
.dp-chart-bar { height: 80px; display: flex; align-items: flex-end; gap: 5px; }
.dp-bar { background: linear-gradient(to top, var(--secondary), #5dd5a8); border-radius: 4px 4px 0 0; flex: 1; min-width: 0; transition: height 0.5s ease; }
.dp-row { display: flex; gap: 8px; padding: 0 12px 12px; }
.dp-card-mini { background: #252535; border-radius: 8px; padding: 10px; flex: 1; }
.dp-score-ring { width: 50px; height: 50px; margin: 0 auto; }

/* Sections */
.pub-section { padding: 100px 40px; max-width: 1200px; margin: 0 auto; }
.section-eyebrow { color: var(--secondary); font-size: 13px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 12px; }
.section-title { font-family: 'Poppins', sans-serif; font-size: 40px; font-weight: 700; color: var(--primary); margin-bottom: 16px; line-height: 1.2; }
.section-sub { color: var(--text-muted); font-size: 16px; line-height: 1.6; max-width: 600px; }

/* Avantages */
.advantages-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 56px; }
.adv-card {
  background: #fff; border-radius: var(--radius); padding: 32px; border: 1px solid var(--border);
  box-shadow: var(--shadow); transition: all 0.3s ease; position: relative; overflow: hidden;
}
.adv-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--secondary), var(--primary));
  transform: scaleX(0); transition: transform 0.3s ease; transform-origin: left;
}
.adv-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.adv-card:hover::before { transform: scaleX(1); }
.adv-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 20px; }
.adv-title { font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 17px; color: var(--primary); margin-bottom: 10px; }
.adv-text { color: var(--text-muted); font-size: 14px; line-height: 1.6; }

/* Timeline How It Works */
.timeline-section { background: linear-gradient(135deg, var(--primary) 0%, #0d2540 100%); padding: 100px 0; }
.timeline-inner { max-width: 1200px; margin: 0 auto; padding: 0 40px; }
.timeline-steps { display: grid; grid-template-columns: repeat(4,1fr); gap: 0; margin-top: 56px; position: relative; }
.timeline-steps::before {
  content: ''; position: absolute; top: 32px; left: 12.5%; right: 12.5%;
  height: 2px; background: linear-gradient(90deg, var(--secondary) 0%, rgba(46,175,125,0.3) 100%);
}
.timeline-step { text-align: center; position: relative; padding: 0 16px; }
.step-num {
  width: 64px; height: 64px; border-radius: 50%; background: var(--neutral-dark);
  border: 3px solid var(--secondary); display: flex; align-items: center; justify-content: center;
  font-family: 'Poppins', sans-serif; font-size: 22px; font-weight: 700; color: var(--secondary);
  margin: 0 auto 24px; position: relative; z-index: 2; transition: all 0.3s;
}
.timeline-step:hover .step-num { background: var(--secondary); color: #fff; transform: scale(1.1); }
.step-title { font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #fff; margin-bottom: 10px; }
.step-desc { color: rgba(255,255,255,0.6); font-size: 13px; line-height: 1.6; }

/* Map section */
.map-section { padding: 100px 40px; }
.map-section-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.5fr; gap: 60px; align-items: center; }
#africa-map { height: 420px; border-radius: var(--radius); box-shadow: var(--shadow-md); border: 1px solid var(--border); }

/* Stats counter */
.stats-section { background: var(--primary); padding: 80px 40px; }
.stats-inner { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: repeat(4,1fr); gap: 40px; }
.stat-item { text-align: center; }
.stat-num { font-family: 'Poppins', sans-serif; font-size: 48px; font-weight: 800; color: var(--secondary); line-height: 1; }
.stat-label { color: rgba(255,255,255,0.7); font-size: 14px; margin-top: 8px; }

/* CTA Final */
.cta-final { background: linear-gradient(135deg, var(--secondary), #1a8a5e); padding: 80px 40px; text-align: center; }
.cta-final h2 { font-family: 'Poppins', sans-serif; font-size: 36px; font-weight: 700; color: #fff; margin-bottom: 12px; }
.cta-final p { color: rgba(255,255,255,0.85); font-size: 16px; margin-bottom: 32px; }

/* Footer */
.pub-footer { background: #0d2540; padding: 60px 40px 30px; }
.footer-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 48px; }
.footer-brand p { color: rgba(255,255,255,0.5); font-size: 14px; line-height: 1.7; margin-top: 16px; }
.footer-col h4 { color: #fff; font-size: 14px; font-weight: 600; margin-bottom: 16px; }
.footer-col a { display: block; color: rgba(255,255,255,0.5); font-size: 14px; margin-bottom: 10px; text-decoration: none; transition: color 0.2s; }
.footer-col a:hover { color: var(--secondary); }
.footer-bottom { max-width: 1200px; margin: 40px auto 0; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center; }
.footer-bottom p { color: rgba(255,255,255,0.3); font-size: 13px; }

/* ══════════════════════════════════════════
   AUTH PAGES
══════════════════════════════════════════ */
#page-login {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #0d2540 0%, #1A3C5E 50%, #0d3330 100%);
  position: relative; overflow: hidden;
}
.auth-bg-orbs { position: absolute; inset: 0; pointer-events: none; }
.orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.25; }
.orb1 { width: 400px; height: 400px; background: var(--secondary); top: -100px; right: -100px; }
.orb2 { width: 300px; height: 300px; background: var(--accent); bottom: -100px; left: -100px; }
.auth-card {
  background: rgba(255,255,255,0.04); backdrop-filter: blur(30px);
  border: 1px solid rgba(255,255,255,0.1); border-radius: 20px;
  padding: 48px; width: 440px; position: relative; z-index: 2;
}
.auth-logo { text-align: center; margin-bottom: 36px; }
.auth-logo .logo-big { width: 56px; height: 56px; background: var(--secondary); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 26px; color: #fff; margin-bottom: 16px; }
.auth-logo h1 { font-family: 'Poppins', sans-serif; font-size: 28px; font-weight: 700; color: #fff; }
.auth-logo p { color: rgba(255,255,255,0.5); font-size: 14px; margin-top: 6px; }
.form-group { margin-bottom: 20px; }
.form-label { display: block; color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 500; margin-bottom: 8px; }
.form-input {
  width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; color: #fff;
  font-size: 14px; font-family: 'Inter', sans-serif; transition: all 0.2s;
  outline: none;
}
.form-input::placeholder { color: rgba(255,255,255,0.3); }
.form-input:focus { border-color: var(--secondary); background: rgba(255,255,255,0.08); box-shadow: 0 0 0 3px rgba(46,175,125,0.2); }
.form-input-light {
  width: 100%; padding: 11px 16px; background: #fff; border: 1.5px solid var(--border);
  border-radius: 10px; color: var(--text-main); font-size: 14px; font-family: 'Inter', sans-serif;
  transition: all 0.2s; outline: none;
}
.form-input-light:focus { border-color: var(--secondary); box-shadow: 0 0 0 3px rgba(46,175,125,0.12); }
.form-input-group { position: relative; }
.form-input-group .form-input { padding-left: 44px; }
.form-input-group .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.4); font-size: 16px; }
.input-group-light .form-input-light { padding-left: 44px; }
.input-group-light .input-icon-light { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 16px; }

/* OTP Inputs */
.otp-inputs { display: flex; gap: 10px; justify-content: center; margin: 24px 0; }
.otp-input {
  width: 52px; height: 60px; text-align: center; font-size: 24px; font-weight: 700;
  font-family: 'JetBrains Mono', monospace; background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; color: #fff;
  outline: none; transition: all 0.2s;
}
.otp-input:focus { border-color: var(--secondary); background: rgba(46,175,125,0.1); box-shadow: 0 0 0 3px rgba(46,175,125,0.2); }
.otp-filled { border-color: var(--secondary); background: rgba(46,175,125,0.15); }

/* ══════════════════════════════════════════
   ADMIN DASHBOARD LAYOUT
══════════════════════════════════════════ */
#page-dashboard {
  display: none; flex-direction: row; min-height: 100vh; background: var(--neutral-dark);
}
#page-dashboard.active { display: flex; }

/* Sidebar */
.sidebar {
  width: var(--sidebar-w); background: var(--neutral-dark2); border-right: 1px solid var(--border-dark);
  display: flex; flex-direction: column; position: fixed; left: 0; top: 0; bottom: 0; z-index: 100;
  transition: transform 0.3s ease;
}
.sidebar.collapsed { transform: translateX(-100%); }
.sidebar-header { padding: 20px; border-bottom: 1px solid var(--border-dark); }
.sidebar-logo { display: flex; align-items: center; gap: 10px; }
.sidebar-logo .logo-icon { width: 36px; height: 36px; background: var(--secondary); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px; }
.sidebar-logo span { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; color: #fff; }
.sidebar-logo span em { color: var(--secondary); font-style: normal; }

.sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
.nav-section-label { color: #555; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; padding: 16px 8px 8px; }
.sidebar-item {
  display: flex; align-items: center; gap: 12px; padding: 11px 12px; border-radius: 10px;
  color: rgba(255,255,255,0.55); font-size: 14px; font-weight: 500; cursor: pointer;
  transition: all 0.2s; margin-bottom: 2px; text-decoration: none;
}
.sidebar-item i { width: 18px; text-align: center; font-size: 15px; }
.sidebar-item:hover { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.9); }
.sidebar-item.active { background: rgba(46,175,125,0.15); color: var(--secondary); }
.sidebar-item.active i { color: var(--secondary); }
.sidebar-badge { margin-left: auto; background: var(--danger); color: #fff; border-radius: 10px; padding: 2px 8px; font-size: 11px; font-weight: 600; }
.sidebar-toggle-pill {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 12px; border-radius: 8px; margin-bottom: 4px;
}
.toggle-switch {
  width: 36px; height: 20px; background: #444; border-radius: 10px; position: relative;
  cursor: pointer; transition: background 0.3s;
}
.toggle-switch.on { background: var(--secondary); }
.toggle-knob {
  position: absolute; top: 2px; left: 2px; width: 16px; height: 16px;
  background: #fff; border-radius: 50%; transition: left 0.3s;
}
.toggle-switch.on .toggle-knob { left: 18px; }

.sidebar-user {
  padding: 16px; border-top: 1px solid var(--border-dark);
  display: flex; align-items: center; gap: 10px;
}
.user-avatar {
  width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--secondary), var(--primary));
  display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 14px; flex-shrink: 0;
}
.user-info { flex: 1; min-width: 0; }
.user-name { color: #fff; font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { color: #666; font-size: 11px; }

/* Topbar */
.topbar {
  height: var(--topbar-h); background: var(--neutral-dark2);
  border-bottom: 1px solid var(--border-dark);
  display: flex; align-items: center; padding: 0 24px;
  gap: 16px; position: fixed; top: 0; right: 0; z-index: 99;
  left: var(--sidebar-w); transition: left 0.3s ease;
}
.topbar.expanded { left: 0; }
.topbar-toggle { background: none; border: none; color: rgba(255,255,255,0.6); font-size: 18px; cursor: pointer; padding: 6px; border-radius: 6px; }
.topbar-toggle:hover { color: #fff; background: rgba(255,255,255,0.06); }
.breadcrumb { display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.5); font-size: 14px; flex: 1; }
.breadcrumb .current { color: #fff; font-weight: 500; }
.breadcrumb .sep { color: #444; }

.topbar-search {
  position: relative; width: 240px;
}
.topbar-search input {
  width: 100%; padding: 8px 12px 8px 36px; background: rgba(255,255,255,0.06);
  border: 1px solid var(--border-dark); border-radius: 8px; color: rgba(255,255,255,0.8);
  font-size: 13px; outline: none; transition: all 0.2s;
}
.topbar-search input::placeholder { color: rgba(255,255,255,0.3); }
.topbar-search input:focus { background: rgba(255,255,255,0.09); border-color: var(--secondary); }
.topbar-search .search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.3); font-size: 13px; }

.topbar-actions { display: flex; align-items: center; gap: 8px; }
.topbar-btn {
  width: 36px; height: 36px; border-radius: 8px; border: none; background: rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.6); cursor: pointer; display: flex; align-items: center; justify-content: center;
  font-size: 15px; transition: all 0.2s; position: relative;
}
.topbar-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
.notif-dot { position: absolute; top: 6px; right: 6px; width: 8px; height: 8px; background: var(--danger); border-radius: 50%; border: 2px solid var(--neutral-dark2); }

/* Main content */
.main-content {
  margin-left: var(--sidebar-w); margin-top: var(--topbar-h);
  padding: 28px; min-height: calc(100vh - var(--topbar-h)); width: calc(100% - var(--sidebar-w));
  transition: all 0.3s ease;
}
.main-content.expanded { margin-left: 0; width: 100%; }

/* Dashboard modules */
.dash-module { display: none; }
.dash-module.active { display: block; }

/* ══════════════════════════════════════════
   MODULE: APERÇU
══════════════════════════════════════════ */
.module-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; }
.module-title { font-family: 'Poppins', sans-serif; font-size: 24px; font-weight: 700; color: #fff; }
.module-sub { color: rgba(255,255,255,0.4); font-size: 14px; margin-top: 4px; }
.module-actions { display: flex; gap: 10px; align-items: center; }

/* KPI Cards */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.kpi-card {
  background: var(--neutral-dark2); border: 1px solid var(--border-dark);
  border-radius: var(--radius); padding: 20px; cursor: grab;
}
.kpi-card-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px; }
.kpi-icon {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.kpi-trend { display: flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; }
.kpi-trend.up { color: var(--secondary); }
.kpi-trend.down { color: var(--danger); }
.kpi-val { font-family: 'Poppins', sans-serif; font-size: 32px; font-weight: 700; color: #fff; line-height: 1; margin-bottom: 4px; }
.kpi-label { color: rgba(255,255,255,0.45); font-size: 13px; }
.kpi-bar { height: 3px; background: rgba(255,255,255,0.08); border-radius: 2px; margin-top: 14px; }
.kpi-bar-fill { height: 100%; border-radius: 2px; transition: width 1s ease; }

/* Charts Grid */
.charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px; }
.chart-card {
  background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); padding: 20px;
}
.chart-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.chart-title { color: #fff; font-weight: 600; font-size: 15px; }
.chart-sub { color: rgba(255,255,255,0.35); font-size: 12px; margin-top: 2px; }
.chart-actions { display: flex; gap: 4px; }
.chart-btn { padding: 4px 10px; border-radius: 6px; font-size: 12px; border: 1px solid var(--border-dark); background: transparent; color: rgba(255,255,255,0.4); cursor: pointer; transition: all 0.2s; }
.chart-btn.active, .chart-btn:hover { background: var(--secondary); color: #fff; border-color: var(--secondary); }

.charts-row2 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 24px; }

/* Tables */
.table-card {
  background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); padding: 20px;
}
.tables-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px; margin-bottom: 24px; }
.dash-table { width: 100%; border-collapse: collapse; }
.dash-table th { text-align: left; color: rgba(255,255,255,0.35); font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; padding: 0 12px 10px; border-bottom: 1px solid var(--border-dark); }
.dash-table td { padding: 12px; color: rgba(255,255,255,0.8); font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.04); }
.dash-table tr:last-child td { border-bottom: none; }
.dash-table tr:hover td { background: rgba(255,255,255,0.02); }
.candidate-name { display: flex; align-items: center; gap: 8px; }
.candidate-av { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #fff; }

/* Score Ring SVG */
.score-ring-wrap { display: flex; flex-direction: column; align-items: center; padding: 16px; }
.score-ring-label { color: rgba(255,255,255,0.5); font-size: 11px; margin-top: 8px; text-align: center; }
.score-ring-val { font-family: 'Poppins', sans-serif; font-size: 22px; font-weight: 700; }

/* Calendar mini */
.mini-cal-card { background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); padding: 20px; }
.mini-events { margin-top: 12px; }
.mini-event { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 8px; margin-bottom: 6px; }
.mini-event-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.mini-event-info { flex: 1; }
.mini-event-title { color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 500; }
.mini-event-date { color: rgba(255,255,255,0.35); font-size: 11px; }

/* ══════════════════════════════════════════
   MODULE: PROJETS
══════════════════════════════════════════ */
.filter-bar {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius);
  padding: 16px 20px; margin-bottom: 20px;
}
.filter-search { position: relative; flex: 1; min-width: 200px; }
.filter-search input {
  width: 100%; padding: 9px 12px 9px 36px; background: rgba(255,255,255,0.06);
  border: 1px solid var(--border-dark); border-radius: 8px; color: #fff; font-size: 13px; outline: none;
}
.filter-search input::placeholder { color: rgba(255,255,255,0.3); }
.filter-search input:focus { border-color: var(--secondary); }
.filter-search .search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.3); }
.filter-select {
  padding: 9px 32px 9px 12px; background: rgba(255,255,255,0.06);
  border: 1px solid var(--border-dark); border-radius: 8px; color: rgba(255,255,255,0.7);
  font-size: 13px; outline: none; appearance: none; cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 10px center;
}

.projects-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.project-card {
  background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius);
  overflow: visible; position: relative; z-index: 1; transition: all 0.3s; cursor: default;
}
.project-card:hover { border-color: rgba(46,175,125,0.4); transform: translateY(-2px); z-index: 3; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
.project-card-img {
  height: 140px; position: relative; overflow: hidden;
  display: flex; align-items: center; justify-content: center;
  border-radius: var(--radius) var(--radius) 0 0;
}
.project-card-img .proj-icon { font-size: 40px; opacity: 0.6; }
.project-card-body { padding: 20px; }
.project-card-meta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.project-card-title { color: #fff; font-weight: 600; font-size: 15px; margin-bottom: 6px; font-family: 'Poppins', sans-serif; }
.project-card-org { color: rgba(255,255,255,0.4); font-size: 13px; display: flex; align-items: center; gap: 6px; margin-bottom: 14px; }
.project-card-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.06); }
.project-card .btn-preview-organisation { position: absolute; left: 50%; top: 140px; transform: translate(-50%, -50%); z-index: 10 !important; background: #fff; color: var(--neutral-dark); border: 2px solid var(--neutral-dark2); box-shadow: 0 4px 12px rgba(0,0,0,0.24); }
.project-card .btn-preview-organisation:hover { background: var(--secondary); color: #fff; transform: translate(-50%, -50%) scale(1.06); }
.project-card-body { padding-top: 28px; }
.project-stat { text-align: center; }
.project-stat-val { font-family: 'JetBrains Mono', monospace; font-size: 16px; font-weight: 600; color: #fff; }
.project-stat-label { color: rgba(255,255,255,0.35); font-size: 11px; }
.project-card-actions { display: flex; gap: 4px; }

/* ══════════════════════════════════════════
   MODULE: CRITÈRES
══════════════════════════════════════════ */
.criteria-layout { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
.criteria-list { background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); }
.criteria-list-header { padding: 20px; border-bottom: 1px solid var(--border-dark); display: flex; align-items: center; justify-content: space-between; }
.criteria-item {
  display: flex; align-items: center; gap: 14px; padding: 16px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.04); cursor: grab; transition: background 0.2s;
}
.criteria-item:hover { background: rgba(255,255,255,0.03); }
.criteria-item:last-child { border-bottom: none; }
.drag-handle { color: rgba(255,255,255,0.2); font-size: 16px; cursor: grab; }
.criteria-num { width: 32px; height: 32px; border-radius: 8px; background: rgba(46,175,125,0.15); color: var(--secondary); font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-family: 'JetBrains Mono', monospace; }
.criteria-info { flex: 1; }
.criteria-label { color: #fff; font-weight: 500; font-size: 14px; }
.criteria-desc { color: rgba(255,255,255,0.35); font-size: 12px; margin-top: 2px; }
.criteria-weights { display: flex; gap: 16px; align-items: center; }
.weight-pill { background: rgba(245,166,35,0.15); color: var(--accent); padding: 3px 10px; border-radius: 6px; font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 600; }
.score-pill { background: rgba(26,60,94,0.4); color: rgba(255,255,255,0.6); padding: 3px 10px; border-radius: 6px; font-family: 'JetBrains Mono', monospace; font-size: 12px; }

.criteria-panel { background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); padding: 20px; }
.panel-title { color: #fff; font-weight: 600; font-size: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

/* Form builder */
.form-builder-wrap { display: grid; grid-template-columns: 220px 1fr 240px; gap: 0; height: calc(100vh - var(--topbar-h) - 56px - 80px); }
.fb-sidebar { background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-right: none; border-radius: var(--radius) 0 0 var(--radius); padding: 16px; overflow-y: auto; }
.fb-canvas { background: #1a1a2e; border: 1px solid var(--border-dark); position: relative; overflow: auto; min-height: 400px; }
.fb-canvas-inner { min-height: 700px; position: relative; padding: 20px; }
.fb-props { background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-left: none; border-radius: 0 var(--radius) var(--radius) 0; padding: 16px; overflow-y: auto; }
.fb-section-title { color: rgba(255,255,255,0.3); font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid var(--border-dark); }
.fb-component {
  display: flex; align-items: center; gap: 8px; padding: 9px 10px; border-radius: 8px;
  color: rgba(255,255,255,0.6); font-size: 12px; cursor: grab; margin-bottom: 4px;
  border: 1px solid transparent; transition: all 0.2s;
}
.fb-component:hover { background: rgba(255,255,255,0.06); border-color: var(--border-dark); color: rgba(255,255,255,0.9); }
.fb-component i { width: 16px; text-align: center; color: var(--secondary); }
.canvas-toolbar {
  padding: 10px 20px; background: var(--neutral-dark3); border-bottom: 1px solid var(--border-dark);
  display: flex; align-items: center; gap: 12px;
}
.canvas-element {
  background: rgba(255,255,255,0.05); border: 1.5px dashed rgba(255,255,255,0.15);
  border-radius: 8px; padding: 12px 14px; margin-bottom: 10px; cursor: move;
  transition: all 0.2s; position: relative;
}
.canvas-element:hover { border-color: var(--secondary); background: rgba(46,175,125,0.05); }
.canvas-element.selected { border-color: var(--secondary); border-style: solid; background: rgba(46,175,125,0.08); }
.elem-label { color: rgba(255,255,255,0.5); font-size: 11px; margin-bottom: 6px; font-weight: 500; }
.elem-input-mock { height: 34px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; display: flex; align-items: center; padding: 0 10px; color: rgba(255,255,255,0.25); font-size: 12px; }
.elem-controls { position: absolute; top: 6px; right: 6px; display: none; gap: 4px; }
.canvas-element:hover .elem-controls { display: flex; }
.elem-btn { width: 22px; height: 22px; border-radius: 4px; border: none; cursor: pointer; font-size: 10px; display: flex; align-items: center; justify-content: center; }

/* ══════════════════════════════════════════
   MODULE: ÉVALUATIONS
══════════════════════════════════════════ */
.eval-layout { display: grid; grid-template-columns: 1fr 380px; gap: 20px; }
.eval-candidate-view { background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); padding: 24px; }
.eval-scoring { background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); }
.eval-scoring-header { padding: 20px; border-bottom: 1px solid var(--border-dark); }
.score-ring-big { width: 140px; height: 140px; margin: 0 auto 16px; }
.criteria-score-item { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); }
.criteria-score-label { color: rgba(255,255,255,0.8); font-size: 14px; font-weight: 500; display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.criteria-score-max { color: rgba(255,255,255,0.3); font-size: 12px; }
.slider-wrap { display: flex; align-items: center; gap: 12px; }
.score-slider-track { flex: 1; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; position: relative; cursor: pointer; }
.score-slider-fill { height: 100%; background: linear-gradient(90deg, var(--secondary), #5dd5a8); border-radius: 3px; transition: width 0.1s; }
.score-slider-thumb { width: 18px; height: 18px; border-radius: 50%; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.3); position: absolute; top: 50%; transform: translateY(-50%); cursor: pointer; transition: transform 0.1s; }
.score-slider-thumb:hover { transform: translateY(-50%) scale(1.2); }
.score-input-mini { width: 52px; background: rgba(255,255,255,0.08); border: 1px solid var(--border-dark); border-radius: 6px; color: var(--secondary); font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 600; text-align: center; padding: 4px; outline: none; }

/* Rankings */
.rank-table { width: 100%; border-collapse: collapse; }
.rank-table th { color: rgba(255,255,255,0.3); font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; padding: 12px 16px; border-bottom: 1px solid var(--border-dark); text-align: left; }
.rank-table td { padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.04); color: rgba(255,255,255,0.8); font-size: 14px; }
.rank-table tr:hover td { background: rgba(255,255,255,0.02); }
.rank-medal { font-size: 20px; }
.rank-score-bar { height: 6px; background: rgba(255,255,255,0.06); border-radius: 3px; margin-top: 4px; }
.rank-score-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--secondary), #5dd5a8); }

/* Eval levels */
.levels-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.level-card {
  background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); padding: 20px;
  border-left: 4px solid transparent; transition: all 0.3s;
}
.level-card:hover { transform: translateY(-2px); }
.level-range { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: rgba(255,255,255,0.4); margin-bottom: 8px; }
.level-name { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 6px; }
.level-desc { color: rgba(255,255,255,0.45); font-size: 13px; line-height: 1.5; }

/* ══════════════════════════════════════════
   SETTINGS PAGE
══════════════════════════════════════════ */
.settings-grid { display: grid; grid-template-columns: 240px 1fr; gap: 24px; }
.settings-nav { background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); padding: 12px; }
.settings-nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; color: rgba(255,255,255,0.5); font-size: 14px; cursor: pointer; transition: all 0.2s; margin-bottom: 2px; }
.settings-nav-item:hover { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.8); }
.settings-nav-item.active { background: rgba(46,175,125,0.15); color: var(--secondary); }
.settings-panel { background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); padding: 28px; }
.settings-section { margin-bottom: 32px; }
.settings-section-title { color: #fff; font-weight: 600; font-size: 15px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-dark); display: flex; align-items: center; gap: 8px; }
.settings-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
.settings-row:last-child { border-bottom: none; }
.settings-label { color: rgba(255,255,255,0.8); font-size: 14px; font-weight: 500; }
.settings-desc { color: rgba(255,255,255,0.35); font-size: 12px; margin-top: 3px; }
.form-input-dark {
  background: rgba(255,255,255,0.06); border: 1px solid var(--border-dark); border-radius: 8px;
  color: rgba(255,255,255,0.8); color-scheme: dark; font-size: 14px; padding: 9px 14px; outline: none; font-family: 'Inter', sans-serif;
}
.form-input-dark:focus { border-color: var(--secondary); }
.form-input-dark option,
.filter-select option {
  color: #f8fafc;
  background-color: var(--neutral-dark2);
}

/* ══════════════════════════════════════════
   CANDIDATE FORM PAGE
══════════════════════════════════════════ */
#page-candidate {
  min-height: 100vh; background: var(--neutral-light);
}
.cand-nav { height: 60px; background: var(--primary); display: flex; align-items: center; padding: 0 32px; gap: 12px; }
.cand-progress-bar { background: rgba(255,255,255,0.1); height: 4px; }
.cand-progress-fill { height: 100%; background: var(--secondary); transition: width 0.5s ease; }
.cand-form-wrap { max-width: 760px; margin: 0 auto; padding: 40px 24px; }
.cand-form-header { background: #fff; border-radius: var(--radius); padding: 28px; margin-bottom: 20px; box-shadow: var(--shadow); border-left: 4px solid var(--secondary); }
.cand-form-title { font-family: 'Poppins', sans-serif; font-size: 22px; font-weight: 700; color: var(--primary); margin-bottom: 6px; }
.cand-form-desc { color: var(--text-muted); font-size: 14px; line-height: 1.6; }
.cand-section { background: #fff; border-radius: var(--radius); padding: 24px; margin-bottom: 16px; box-shadow: var(--shadow); }
.cand-section-title { font-family: 'Poppins', sans-serif; font-weight: 600; color: var(--primary); font-size: 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
.cand-field { margin-bottom: 20px; }
.cand-label { display: block; font-size: 14px; font-weight: 500; color: var(--text-main); margin-bottom: 8px; }
.cand-label .req { color: var(--danger); }
.progress-info { display: flex; justify-content: space-between; align-items: center; color: var(--text-muted); font-size: 13px; margin-bottom: 8px; }
.autosave { display: flex; align-items: center; gap: 6px; color: var(--secondary); font-size: 12px; }

/* ══════════════════════════════════════════
   CALENDAR MODULE
══════════════════════════════════════════ */
.cal-wrap { background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius); padding: 24px; }
.fc-toolbar-title { color: #fff !important; font-family: 'Poppins', sans-serif !important; font-size: 18px !important; }
.fc-button { background: rgba(255,255,255,0.06) !important; border-color: var(--border-dark) !important; color: rgba(255,255,255,0.7) !important; }
.fc-button:hover { background: var(--secondary) !important; border-color: var(--secondary) !important; color: #fff !important; }
.fc-button-active { background: var(--secondary) !important; border-color: var(--secondary) !important; }
.fc-day-header { color: rgba(255,255,255,0.4) !important; font-size: 12px !important; }
.fc-day { background: transparent !important; }
.fc-other-month { opacity: 0.3; }
.fc-day-number { color: rgba(255,255,255,0.6) !important; }
.fc td, .fc th { border-color: var(--border-dark) !important; }

/* ══════════════════════════════════════════
   MODALS
══════════════════════════════════════════ */
.modal-overlay {
  display: none; position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);
  align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal {
  background: var(--neutral-dark2); border: 1px solid var(--border-dark);
  border-radius: 16px; width: 90%; max-width: 560px; max-height: 90vh; overflow-y: auto;
  animation: modalIn 0.3s ease;
}
@keyframes modalIn { from { opacity: 0; transform: scale(0.95) translateY(-10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.modal-header { padding: 24px 24px 0; display: flex; align-items: center; justify-content: space-between; }
.modal-title { color: #fff; font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; }
.modal-close { width: 32px; height: 32px; border-radius: 8px; border: none; background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.6); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; }
.modal-body { padding: 24px; }
.modal-footer { padding: 0 24px 24px; display: flex; justify-content: flex-end; gap: 10px; }

/* Notification toast */
.toast {
  position: fixed; bottom: 24px; right: 24px; z-index: 99999;
  background: var(--neutral-dark2); border: 1px solid var(--border-dark);
  border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; gap: 12px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.4); animation: toastIn 0.4s ease; min-width: 280px;
}
.toast.success { border-left: 4px solid var(--secondary); }
.toast.warning { border-left: 4px solid var(--accent); }
.toast.error { border-left: 4px solid var(--danger); }
.toast-icon { font-size: 18px; }
.toast-text { color: rgba(255,255,255,0.85); font-size: 14px; font-weight: 500; }
@keyframes toastIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

/* ══════════════════════════════════════════
   LEAFLET MAP OVERRIDES
══════════════════════════════════════════ */
.leaflet-container { background: #0d2540 !important; }
.leaflet-tile-pane { filter: hue-rotate(180deg) invert(0.85) saturate(0.5); }

/* ══════════════════════════════════════════
   SCROLLBAR
══════════════════════════════════════════ */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

/* ══════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════ */
@media (max-width: 1024px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .charts-row2 { grid-template-columns: 1fr 1fr; }
  .projects-grid { grid-template-columns: repeat(2, 1fr); }
  .advantages-grid { grid-template-columns: repeat(2, 1fr); }
  .hero-content { grid-template-columns: 1fr; gap: 40px; }
  .hero-visual { display: none; }
  .hero-title { font-size: 38px; }
}
@media (max-width: 768px) {
  .pub-nav .nav-links { display: none; }
  .kpi-grid { grid-template-columns: 1fr 1fr; }
  .advantages-grid { grid-template-columns: 1fr; }
  .stats-inner { grid-template-columns: repeat(2,1fr); }
  .footer-grid { grid-template-columns: 1fr 1fr; }
  .sidebar { transform: translateX(-100%); }
  .sidebar.mobile-open { transform: translateX(0); }
  .topbar { left: 0; }
  .main-content { margin-left: 0; width: 100%; }
}

/* Animations */
@keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeInRight { from { opacity: 0; transform: translateX(-24px); } to { opacity: 1; transform: translateX(0); } }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes countUp { from { opacity: 0; } to { opacity: 1; } }
.animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
.animate-fadeInRight { animation: fadeInRight 0.6s ease forwards; }
.anim-delay-1 { animation-delay: 0.1s; }
.anim-delay-2 { animation-delay: 0.2s; }
.anim-delay-3 { animation-delay: 0.3s; }
.anim-delay-4 { animation-delay: 0.4s; }
.anim-delay-5 { animation-delay: 0.5s; }

/* ══ ACTIVE SELECTIONS ══ */
.ui-sortable-helper { opacity: 0.8 !important; box-shadow: 0 8px 32px rgba(0,0,0,0.5) !important; }
.ui-datepicker { font-family: 'Inter', sans-serif !important; border-radius: 12px !important; }
</style>
</head>
<body>
<div id="page-dashboard" class="page active">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <div class="logo-icon"><i class="fas fa-chart-line"></i></div>
        <span>Criteval<em>_pro</em></span>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Navigation</div>
      <a class="sidebar-item active" onclick="switchModule('apercu')"><i class="fas fa-home"></i> Aperçu</a>
      <a class="sidebar-item" onclick="switchModule('projets')"><i class="fas fa-building"></i> Organisations <span class="sidebar-badge">3</span></a>
      <a class="sidebar-item" onclick="switchModule('criteres')"><i class="fas fa-check-square"></i> Critères</a>
      <a class="sidebar-item" onclick="switchModule('formulaires')"><i class="fas fa-file-alt"></i> Formulaires</a>
      <a class="sidebar-item" onclick="switchModule('formation')"><i class="fas fa-chalkboard-teacher"></i> Formation</a>
      <a class="sidebar-item" onclick="switchModule('evaluations')"><i class="fas fa-star"></i> Évaluations</a>
      <a class="sidebar-item" onclick="switchModule('classements')"><i class="fas fa-trophy"></i> Classements</a>
      <a class="sidebar-item" onclick="switchModule('calendrier')"><i class="fas fa-calendar-alt"></i> Planning</a>

      <div class="nav-section-label" style="margin-top:8px">Modules</div>
      <div class="sidebar-toggle-pill">
        <span style="color:rgba(255,255,255,0.5);font-size:13px"><i class="fas fa-building" style="width:16px;color:rgba(255,255,255,0.3)"></i> Organisations</span>
        <div class="toggle-switch on" onclick="$(this).toggleClass('on')"><div class="toggle-knob"></div></div>
      </div>
      <div class="sidebar-toggle-pill">
        <span style="color:rgba(255,255,255,0.5);font-size:13px"><i class="fas fa-star" style="width:16px;color:rgba(255,255,255,0.3)"></i> Évaluations</span>
        <div class="toggle-switch on" onclick="$(this).toggleClass('on')"><div class="toggle-knob"></div></div>
      </div>
      <div class="sidebar-toggle-pill">
        <span style="color:rgba(255,255,255,0.5);font-size:13px"><i class="fas fa-file" style="width:16px;color:rgba(255,255,255,0.3)"></i> Formulaires</span>
        <div class="toggle-switch on" onclick="$(this).toggleClass('on')"><div class="toggle-knob"></div></div>
      </div>
      <div class="sidebar-toggle-pill">
        <span style="color:rgba(255,255,255,0.5);font-size:13px"><i class="fas fa-chalkboard-teacher" style="width:16px;color:rgba(255,255,255,0.3)"></i> Formation</span>
        <div class="toggle-switch on" onclick="$(this).toggleClass('on')"><div class="toggle-knob"></div></div>
      </div>

      <div class="nav-section-label" style="margin-top:8px">Configuration</div>
      <a class="sidebar-item" onclick="switchModule('parametres')"><i class="fas fa-cog"></i> Paramètres</a>
    </nav>
    <div class="sidebar-user">
      <?php
        $displayName = $currentUser['name'] ?? 'User';
        $displayRole = $currentUser['role'] ?? 'user';
        $roleLabel = function_exists('role_label') ? role_label($displayRole) : ucfirst($displayRole);
        $avatarInitials = strtoupper(substr($displayName, 0, 1) . (strpos($displayName, ' ') !== false ? substr($displayName, strpos($displayName, ' ') + 1, 1) : ''));
      ?>
      <div class="user-avatar"><?= htmlspecialchars($avatarInitials, ENT_QUOTES, 'UTF-8') ?></div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="user-role"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <button style="background:none;border:none;color:#555;cursor:pointer;font-size:16px;padding:4px" onclick="window.location.href='<?= BASE_URL ?>/logout'" title="Deconnexion"><i class="fas fa-sign-out-alt"></i></button>
    </div>
  </aside>

  <!-- TOPBAR -->
  <header class="topbar" id="topbar">
    <button class="topbar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <div class="breadcrumb">
      <span>Criteval_pro</span>
      <span class="sep">/</span>
      <span class="current" id="breadcrumbCurrent">Aperçu</span>
    </div>
    <div class="topbar-search">
      <i class="fas fa-search search-icon"></i>
      <input type="text" placeholder="Rechercher...">
    </div>
    <div class="topbar-actions">
      <button class="topbar-btn" title="Actualiser"><i class="fas fa-sync-alt"></i></button>
      <button class="topbar-btn" title="Notifications">
        <i class="fas fa-bell"></i>
        <div class="notif-dot"></div>
      </button>
      <button class="topbar-btn" title="Aide"><i class="fas fa-question-circle"></i></button>
      <button class="btn btn-secondary btn-sm" onclick="showToast('Rapport PDF généré avec succès !', 'success')"><i class="fas fa-download"></i> Exporter</button>
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="main-content" id="mainContent">

    <!-- ╔══ MODULE: APERÇU ══╗ -->
    <div class="dash-module active" id="module-apercu">
      <div class="module-header">
        <div>
          <h1 class="module-title">Tableau de bord</h1>
          <p class="module-sub">Vue d'ensemble de vos activités d'évaluation</p>
        </div>
        <div class="module-actions">
          <input type="text" id="daterange" placeholder="Période..." style="padding:8px 12px;background:rgba(255,255,255,0.06);border:1px solid var(--border-dark);border-radius:8px;color:rgba(255,255,255,0.7);font-size:13px;width:160px;outline:none;cursor:pointer">
          <button class="btn btn-ghost-dark btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
          <button class="btn btn-secondary btn-sm" onclick="showToast('Export CSV téléchargé', 'success')"><i class="fas fa-file-csv"></i> CSV</button>
        </div>
      </div>

      <!-- KPI Cards - sortable -->
      <div class="kpi-grid" id="kpiSortable">
        <div class="kpi-card widget-handle">
          <div class="kpi-card-top">
            <div class="kpi-icon" style="background:rgba(46,175,125,0.15);color:var(--secondary)"><i class="fas fa-folder-open"></i></div>
            <div class="kpi-trend up"><i class="fas fa-database"></i> réel</div>
          </div>
          <div class="kpi-val" data-target="<?= (int) ($overviewKpis['active_projects'] ?? 0) ?>"><?= (int) ($overviewKpis['active_projects'] ?? 0) ?></div>
          <div class="kpi-label">Organisations actives</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:70%;background:var(--secondary)"></div></div>
        </div>
        <div class="kpi-card widget-handle">
          <div class="kpi-card-top">
            <div class="kpi-icon" style="background:rgba(245,166,35,0.15);color:var(--accent)"><i class="fas fa-file-alt"></i></div>
            <div class="kpi-trend up"><i class="fas fa-database"></i> réel</div>
          </div>
          <div class="kpi-val" data-target="<?= (int) ($overviewKpis['submissions'] ?? 0) ?>"><?= (int) ($overviewKpis['submissions'] ?? 0) ?></div>
          <div class="kpi-label">Candidatures reçues</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:85%;background:var(--accent)"></div></div>
        </div>
        <div class="kpi-card widget-handle">
          <div class="kpi-card-top">
            <div class="kpi-icon" style="background:rgba(26,60,94,0.4);color:#7eb8e8"><i class="fas fa-star"></i></div>
            <div class="kpi-trend up"><i class="fas fa-database"></i> réel</div>
          </div>
          <div class="kpi-val" data-target="<?= (int) ($overviewKpis['evaluated'] ?? 0) ?>"><?= (int) ($overviewKpis['evaluated'] ?? 0) ?></div>
          <div class="kpi-label">Évaluations complètes</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:76%;background:#7eb8e8"></div></div>
        </div>
        <div class="kpi-card widget-handle">
          <div class="kpi-card-top">
            <div class="kpi-icon" style="background:rgba(231,76,60,0.15);color:var(--danger)"><i class="fas fa-clock"></i></div>
            <div class="kpi-trend down"><i class="fas fa-database"></i> réel</div>
          </div>
          <div class="kpi-val" data-target="<?= (int) ($overviewKpis['pending'] ?? 0) ?>"><?= (int) ($overviewKpis['pending'] ?? 0) ?></div>
          <div class="kpi-label">En attente d'évaluation</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:24%;background:var(--danger)"></div></div>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-grid">
        <div class="chart-card">
          <div class="chart-card-header">
            <div>
              <div class="chart-title">Soumissions par mois</div>
              <div class="chart-sub">Évolution des candidatures sur l'année</div>
            </div>
            <div class="chart-actions">
              <button class="chart-btn active">6M</button>
              <button class="chart-btn">1A</button>
              <button class="chart-btn">Tout</button>
            </div>
          </div>
          <canvas id="chartSubmissions" height="120"></canvas>
        </div>
        <div class="chart-card">
          <div class="chart-card-header">
            <div>
              <div class="chart-title">Répartition par pays</div>
              <div class="chart-sub">Top 5 pays africains</div>
            </div>
          </div>
          <canvas id="chartCountries" height="120"></canvas>
        </div>
      </div>

      <div class="charts-row2">
        <div class="chart-card">
          <div class="chart-card-header">
            <div>
              <div class="chart-title">Scores moyens par projet</div>
              <div class="chart-sub">Sur 20 points</div>
            </div>
          </div>
          <canvas id="chartScores" height="130"></canvas>
        </div>
        <div class="chart-card" style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:20px;">
          <div class="chart-title" style="text-align:center">Score global</div>
          <svg class="score-ring-big" viewBox="0 0 140 140" id="globalScoreRing">
            <circle cx="70" cy="70" r="56" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="10"/>
            <circle cx="70" cy="70" r="56" fill="none" stroke="url(#ringGrad)" stroke-width="10" stroke-linecap="round" stroke-dasharray="351.9" stroke-dashoffset="88" transform="rotate(-90 70 70)" id="scoreRingCircle"/>
            <defs>
              <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#2EAF7D"/>
                <stop offset="100%" stop-color="#5dd5a8"/>
              </linearGradient>
            </defs>
            <text x="70" y="65" text-anchor="middle" fill="#fff" font-size="28" font-weight="700" font-family="Poppins"><?= e((string) ($overviewKpis['weighted_average'] ?? 0)) ?></text>
            <text x="70" y="82" text-anchor="middle" fill="rgba(255,255,255,0.4)" font-size="11">/100 pts</text>
          </svg>
          <div style="text-align:center"><span class="badge badge-success"><i class="fas fa-arrow-up"></i> Excellent</span></div>
        </div>
        <div class="mini-cal-card">
          <div class="chart-title">Échéances à venir</div>
          <div class="mini-events">
            <?php foreach ($overviewUpcoming as $event): ?><div class="mini-event" style="background:rgba(46,175,125,0.08)"><div class="mini-event-dot" style="background:var(--secondary)"></div><div class="mini-event-info"><div class="mini-event-title"><?= e($event['title']) ?></div><div class="mini-event-date"><?= e($event['organization']) ?> · <?= e((string) $event['date']) ?></div></div></div><?php endforeach; ?>
            <?php if (!$overviewUpcoming): ?><div class="mini-event"><div class="mini-event-info"><div class="mini-event-title">Aucune échéance active</div><div class="mini-event-date">Les prochaines échéances apparaîtront ici.</div></div></div><?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Tables -->
      <div class="tables-grid">
        <div class="table-card">
          <div class="chart-card-header" style="margin-bottom:16px">
            <div><div class="chart-title">Dernières candidatures</div><div class="chart-sub">Soumissions récentes à traiter</div></div>
            <button class="btn btn-ghost-dark btn-sm" onclick="switchModule('evaluations')">Voir tout</button>
          </div>
          <table class="dash-table">
            <thead><tr><th>Candidat</th><th>Projet</th><th>Pays</th><th>Statut</th><th>Score</th></tr></thead>
            <tbody><?php foreach ($overviewLatest as $submission): ?><tr><td><?= e($submission['candidate_name'] ?: $submission['candidate_email']) ?></td><td><?= e($submission['project_title'] ?? '—') ?></td><td><?= e($submission['country_code'] ?? '—') ?></td><td><span class="badge <?= in_array($submission['status'], ['evaluated', 'published'], true) ? 'badge-success' : 'badge-warning' ?>"><?= e(ucfirst((string) $submission['status'])) ?></span></td><td class="font-mono"><?= $submission['score'] !== null ? e((string) round((float) $submission['score'], 1)) : '—' ?></td></tr><?php endforeach; ?><?php if (!$overviewLatest): ?><tr><td colspan="5">Aucune candidature enregistrée.</td></tr><?php endif; ?></tbody>
          </table>
        </div>
        <div class="table-card">
          <div class="chart-card-header" style="margin-bottom:16px">
            <div><div class="chart-title">Top classement</div><div class="chart-sub">Meilleures scores pondérés</div></div>
          </div>
          <table class="dash-table">
            <thead><tr><th>Rang</th><th>Candidat</th><th>Score</th></tr></thead>
            <tbody><?php foreach ($overviewRanking as $index => $rank): ?><tr><td><span class="font-mono" style="color:rgba(255,255,255,.5)"><?= (int) ($index + 1) ?></span></td><td><?= e($rank['candidate_name'] ?: 'Candidat') ?></td><td><span class="font-mono" style="color:var(--secondary)"><?= e((string) round((float) $rank['score'], 1)) ?>/20</span></td></tr><?php endforeach; ?><?php if (!$overviewRanking): ?><tr><td colspan="3">Aucun classement disponible.</td></tr><?php endif; ?></tbody>
          </table>
        </div>
      </div>
    </div><!-- /apercu -->

    <!-- ╔══ MODULE: PROJETS ══╗ -->
    <div class="dash-module" id="module-projets">
      <div class="module-header">
        <div><h1 class="module-title">Organisations</h1><p class="module-sub">Gérez les organisations évaluées par CS4ME</p></div>
        <div class="module-actions">
          <button type="button" class="btn btn-secondary" onclick="openNewOrganisationModal()"><i class="fas fa-plus"></i> Nouvelle organisation</button>
        </div>
      </div>
      <div class="filter-bar">
        <div class="filter-search"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Rechercher un projet..."></div>
        <select class="filter-select"><option>Tous les statuts</option><option>Actif</option><option>Brouillon</option><option>Clôturé</option><option>Archivé</option></select>
        <select class="filter-select"><option>Toutes les organisations</option><option>FAO</option><option>PNUD</option><option>Union Africaine</option></select>
        <select class="filter-select"><option>Trier par date</option><option>Trier par nom</option><option>Trier par candidatures</option></select>
      </div>
      <div class="table-card" style="display:none">
        <table class="dash-table">
          <thead><tr><th>Organisation</th><th>Pays</th><th>Domaines</th><th>Statut</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach (($projects ?? []) as $project): ?>
              <tr>
                <td><strong><?= e($project['organization'] ?: $project['title']) ?></strong><div style="color:rgba(255,255,255,0.4);font-size:12px"><?= e($project['title']) ?></div></td>
                <td><?= e($project['country_code'] ?: '—') ?></td>
                <td><?= e($project['domains'] ?: '—') ?></td>
                <td><span class="badge <?= ($project['status'] ?? 'draft') === 'active' ? 'badge-success' : 'badge-muted' ?>"><?= e(ucfirst((string) ($project['status'] ?? 'draft'))) ?></span></td>
                <td><button type="button" class="btn btn-ghost-dark btn-sm" onclick='openOrganisationModal(<?= json_encode($project, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i> Modifier</button></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php $statusLabels = ['draft' => 'Brouillon', 'active' => 'Actif', 'closed' => 'Clôturé', 'archived' => 'Archivé']; ?>
      <div class="projects-grid organisation-cards-grid">
        <?php foreach (($projects ?? []) as $project): ?>
          <?php $projectStatus = (string) ($project['status'] ?? 'draft'); ?>
          <article class="project-card">
            <div class="project-card-img" style="background:linear-gradient(135deg,#1a4a2a,#2EAF7D20)">
              <?php if (!empty($project['logo_path'])): ?><img src="<?= BASE_URL . '/' . e($project['logo_path']) ?>" alt="Logo <?= e($project['organization'] ?: $project['title']) ?>" style="width:100%;height:100%;object-fit:contain;padding:24px"><?php else: ?><i class="fas fa-building proj-icon" style="color:#2EAF7D"></i><?php endif; ?>
            </div>
            <button type="button" class="btn btn-icon btn-preview-organisation" title="Aperçu" aria-label="Aperçu de l’organisation" onclick='openOrganisationPreview(<?= json_encode($project, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-eye"></i></button>
            <div class="project-card-body">
              <div class="project-card-meta"><span class="badge <?= $projectStatus === 'active' ? 'badge-success' : ($projectStatus === 'closed' ? 'badge-warning' : 'badge-muted') ?>"><i class="fas fa-circle" style="font-size:8px"></i> <?= e($statusLabels[$projectStatus] ?? ucfirst($projectStatus)) ?></span><span style="color:rgba(255,255,255,0.3);font-size:12px"><?= e(date('M Y', strtotime((string) ($project['created_at'] ?? 'now')))) ?></span></div>
              <div class="project-card-title"><?= e($project['organization'] ?: $project['title']) ?></div>
              <div class="project-card-org"><i class="fas fa-building"></i> <?= e($project['intervention_zone'] ?: ($project['country_code'] ?: 'Organisation')) ?></div>
              <div class="project-card-footer">
                <div class="project-stat"><div class="project-stat-val"><?= (int) ($project['project_count'] ?? 0) ?></div><div class="project-stat-label">Projets</div></div>
                <div class="project-stat"><div class="project-stat-val"><?= e($project['country_code'] ?: '—') ?></div><div class="project-stat-label">Pays</div></div>
                <div class="project-stat"><div class="project-stat-val"><?= e($project['legal_status'] === 'legal' ? 'Oui' : 'Non') ?></div><div class="project-stat-label">Légale</div></div>
                <div class="project-card-actions">
                  <button type="button" class="btn btn-icon btn-ghost-dark" title="Modifier" onclick='openOrganisationModal(<?= json_encode($project, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button>
                  <button type="button" class="btn btn-icon" style="background:rgba(231,76,60,0.15);color:var(--danger)" title="Supprimer" onclick='confirmOrganisationDelete(<?= (int) $project['id'] ?>, <?= json_encode($project['organization'] ?: $project['title'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-trash"></i></button>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
        <button type="button" class="project-card organisation-create-card" onclick="openNewOrganisationModal()" style="border:2px dashed rgba(46,175,125,0.3);display:flex;align-items:center;justify-content:center;min-height:280px;cursor:pointer;transition:all 0.3s;width:100%;color:inherit" onmouseover="this.style.background='rgba(46,175,125,0.05)'" onmouseout="this.style.background='var(--neutral-dark2)'">
          <div style="text-align:center"><div style="width:52px;height:52px;border-radius:50%;background:rgba(46,175,125,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:22px;color:var(--secondary)"><i class="fas fa-plus"></i></div><div style="color:var(--secondary);font-weight:600;font-size:15px">Nouvelle organisation</div><div style="color:rgba(255,255,255,0.3);font-size:13px;margin-top:6px">Créer une fiche organisation</div></div>
        </button>
      </div>
      <div class="projects-grid legacy-project-grid" style="display:none">
        <div class="project-card">
          <div class="project-card-img" style="background:linear-gradient(135deg,#1a4a2a,#2EAF7D20)"><i class="fas fa-seedling proj-icon" style="color:#2EAF7D"></i></div>
          <div class="project-card-body">
            <div class="project-card-meta"><span class="badge badge-success"><i class="fas fa-circle" style="font-size:8px"></i> Actif</span><span style="color:rgba(255,255,255,0.3);font-size:12px">Nov 2025</span></div>
            <div class="project-card-title">Programme AGRI-2025</div>
            <div class="project-card-org"><i class="fas fa-building"></i> FAO — Bureau Afrique de l'Ouest</div>
            <div class="project-card-footer">
              <div class="project-stat"><div class="project-stat-val">47</div><div class="project-stat-label">Candidatures</div></div>
              <div class="project-stat"><div class="project-stat-val">38</div><div class="project-stat-label">Évaluées</div></div>
              <div class="project-stat"><div class="project-stat-val">8</div><div class="project-stat-label">Critères</div></div>
              <div class="project-card-actions">
                <button class="btn btn-icon btn-ghost-dark" title="Éditer"><i class="fas fa-edit"></i></button>
                <button class="btn btn-icon btn-ghost-dark" title="Dupliquer"><i class="fas fa-copy"></i></button>
                <button class="btn btn-icon" style="background:rgba(231,76,60,0.15);color:var(--danger)" title="Supprimer" onclick="event.stopPropagation();confirmDelete()"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>
        <div class="project-card">
          <div class="project-card-img" style="background:linear-gradient(135deg,#2a1a4a,#7b2eaf20)"><i class="fas fa-lightbulb proj-icon" style="color:#9b59b6"></i></div>
          <div class="project-card-body">
            <div class="project-card-meta"><span class="badge badge-success"><i class="fas fa-circle" style="font-size:8px"></i> Actif</span><span style="color:rgba(255,255,255,0.3);font-size:12px">Oct 2025</span></div>
            <div class="project-card-title">Fonds Innovation Technologique</div>
            <div class="project-card-org"><i class="fas fa-building"></i> PNUD — Direction Digitale</div>
            <div class="project-card-footer">
              <div class="project-stat"><div class="project-stat-val">83</div><div class="project-stat-label">Candidatures</div></div>
              <div class="project-stat"><div class="project-stat-val">71</div><div class="project-stat-label">Évaluées</div></div>
              <div class="project-stat"><div class="project-stat-val">6</div><div class="project-stat-label">Critères</div></div>
              <div class="project-card-actions">
                <button class="btn btn-icon btn-ghost-dark" title="Éditer"><i class="fas fa-edit"></i></button>
                <button class="btn btn-icon btn-ghost-dark" title="Dupliquer"><i class="fas fa-copy"></i></button>
                <button class="btn btn-icon" style="background:rgba(231,76,60,0.15);color:var(--danger)" title="Supprimer" onclick="event.stopPropagation();confirmDelete()"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>
        <div class="project-card">
          <div class="project-card-img" style="background:linear-gradient(135deg,#1a2a4a,#2e7baf20)"><i class="fas fa-heartbeat proj-icon" style="color:#3498db"></i></div>
          <div class="project-card-body">
            <div class="project-card-meta"><span class="badge badge-warning"><i class="fas fa-circle" style="font-size:8px"></i> Clôturé</span><span style="color:rgba(255,255,255,0.3);font-size:12px">Sept 2025</span></div>
            <div class="project-card-title">Fonds Santé Communautaire</div>
            <div class="project-card-org"><i class="fas fa-building"></i> OMS — Afrique subsaharienne</div>
            <div class="project-card-footer">
              <div class="project-stat"><div class="project-stat-val">57</div><div class="project-stat-label">Candidatures</div></div>
              <div class="project-stat"><div class="project-stat-val">57</div><div class="project-stat-label">Évaluées</div></div>
              <div class="project-stat"><div class="project-stat-val">10</div><div class="project-stat-label">Critères</div></div>
              <div class="project-card-actions">
                <button class="btn btn-icon btn-ghost-dark" title="Éditer"><i class="fas fa-edit"></i></button>
                <button class="btn btn-icon btn-ghost-dark" title="Dupliquer"><i class="fas fa-copy"></i></button>
                <button class="btn btn-icon" style="background:rgba(231,76,60,0.15);color:var(--danger)" title="Supprimer" onclick="event.stopPropagation();confirmDelete()"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>
        <div class="project-card">
          <div class="project-card-img" style="background:linear-gradient(135deg,#2a1a1a,#af5e2e20)"><i class="fas fa-graduation-cap proj-icon" style="color:#e67e22"></i></div>
          <div class="project-card-body">
            <div class="project-card-meta"><span class="badge badge-muted">Brouillon</span><span style="color:rgba(255,255,255,0.3);font-size:12px">Déc 2025</span></div>
            <div class="project-card-title">Bourses d'Excellence 2026</div>
            <div class="project-card-org"><i class="fas fa-building"></i> Union Africaine — Education</div>
            <div class="project-card-footer">
              <div class="project-stat"><div class="project-stat-val">0</div><div class="project-stat-label">Candidatures</div></div>
              <div class="project-stat"><div class="project-stat-val">0</div><div class="project-stat-label">Évaluées</div></div>
              <div class="project-stat"><div class="project-stat-val">5</div><div class="project-stat-label">Critères</div></div>
              <div class="project-card-actions">
                <button class="btn btn-secondary btn-sm">Publier</button>
              </div>
            </div>
          </div>
        </div>
        <button type="button" class="project-card" onclick="openNewOrganisationModal()" style="border:2px dashed rgba(46,175,125,0.3);display:flex;align-items:center;justify-content:center;min-height:280px;cursor:pointer;transition:all 0.3s;width:100%;color:inherit" onmouseover="this.style.background='rgba(46,175,125,0.05)'" onmouseout="this.style.background='var(--neutral-dark2)'">
          <div style="text-align:center">
            <div style="width:52px;height:52px;border-radius:50%;background:rgba(46,175,125,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:22px;color:var(--secondary)"><i class="fas fa-plus"></i></div>
            <div style="color:var(--secondary);font-weight:600;font-size:15px">Nouveau projet</div>
            <div style="color:rgba(255,255,255,0.3);font-size:13px;margin-top:6px">Créer une fiche organisation</div>
          </div>
        </button>
      </div>
    </div><!-- /projets -->

    <!-- ╔══ MODULE: CRITÈRES ══╗ -->
    <div class="dash-module" id="module-criteres">
      <div class="module-header">
        <div><h1 class="module-title">Critères d'évaluation</h1><p class="module-sub">Gestion des critères par projet</p></div>
        <div class="module-actions">
          <select class="filter-select" id="criteriaProjectFilter" style="padding:10px 32px 10px 12px">
            <option value="">Toutes les organisations</option>
            <?php foreach ($projects as $project): ?>
              <option value="<?= (int) ($project['id'] ?? 0) ?>"<?= !empty($projects) && (int) $project['id'] === (int) ($projects[0]['id'] ?? 0) ? ' selected' : '' ?>><?= e($project['organization'] ?? $project['title'] ?? 'Organisation') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="criteria-layout">
        <div>
          <div class="criteria-list">
            <div class="criteria-list-header">
              <span id="criteriaCountLabel" style="color:#fff;font-weight:600"><?= count($criteria ?? []) ?> critère(s) défini(s)</span>
              <div style="display:flex;align-items:center;gap:8px">
                <span style="color:rgba(255,255,255,0.4);font-size:13px">Poids total :</span>
                <span id="criteriaTotalWeight" class="weight-pill">0.00</span>
              </div>
            </div>
            <div id="criteriaList"></div>
          </div>
        </div>
        <div>
          <form id="criteriaForm" class="criteria-panel" style="margin-bottom:16px">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="panel-title"><i id="criteriaFormIcon" class="fas fa-plus" style="color:var(--secondary)"></i> <span id="criteriaFormTitle">Nouveau critère</span></div>
            <div class="form-group" style="margin-bottom:16px">
              <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px">Organisation *</label>
              <select name="project_id" class="form-input-dark" style="width:100%" required>
                <option value="">Choisir une organisation</option>
                <?php foreach ($projects as $project): ?>
                  <option value="<?= (int) ($project['id'] ?? 0) ?>"><?= e($project['organization'] ?? $project['title'] ?? 'Organisation') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:16px">
              <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px">Libellé *</label>
              <input type="text" name="label" class="form-input-dark" style="width:100%" placeholder="Ex: Pertinence du projet" required>
            </div>
            <div class="form-group" style="margin-bottom:16px">
              <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px">Description</label>
              <textarea name="description" class="form-input-dark" style="width:100%;height:70px;resize:none" placeholder="Décrivez ce critère..."></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
              <div>
                <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px">Note max</label>
                <input type="number" name="max_score" class="form-input-dark" style="width:100%" value="20" min="1" step="0.5">
              </div>
              <div>
                <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px">Coefficient</label>
                <input type="number" name="weight" class="form-input-dark" style="width:100%" value="1.00" min="0.25" step="0.25">
              </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
              <span style="color:rgba(255,255,255,0.6);font-size:13px">Obligatoire</span>
              <label class="toggle-switch on" style="cursor:pointer;display:inline-flex">
                <input type="checkbox" name="is_required" checked style="display:none">
                <span class="toggle-knob"></span>
              </label>
            </div>
            <button id="criteriaFormSubmit" type="submit" class="btn btn-secondary" style="width:100%;justify-content:center"><i class="fas fa-save"></i> <span>Enregistrer</span></button>
            <button id="criteriaFormCancel" type="button" class="btn btn-ghost-dark hidden" style="width:100%;justify-content:center;margin-top:8px"><i class="fas fa-times"></i> Annuler la modification</button>
          </form>
          <div class="chart-card">
            <div class="chart-title" style="margin-bottom:16px">Répartition des poids</div>
            <canvas id="chartCriteria" height="160"></canvas>
          </div>
        </div>
      </div>
    </div><!-- /criteres -->

    <!-- ╔══ MODULE: FORMULAIRES ══╗ -->
    <div class="dash-module" id="module-formulaires">
      <div class="module-header">
        <div><h1 class="module-title">Formulaires</h1><p class="module-sub">Galerie, constructeur et planification</p></div>
        <div class="module-actions">
          <div style="display:flex;gap:4px;background:rgba(255,255,255,0.06);border:1px solid var(--border-dark);border-radius:8px;padding:4px">
            <button class="tab-btn active" onclick="switchFormTab('galerie',this)" style="padding:6px 14px;border-radius:6px;border:none;font-size:13px;cursor:pointer;background:var(--secondary);color:#fff">Galerie</button>
            <button class="tab-btn" onclick="switchFormTab('builder',this)" style="padding:6px 14px;border-radius:6px;border:none;font-size:13px;cursor:pointer;background:transparent;color:rgba(255,255,255,0.5)">Builder</button>
            <button class="tab-btn" onclick="switchFormTab('planning',this)" style="padding:6px 14px;border-radius:6px;border:none;font-size:13px;cursor:pointer;background:transparent;color:rgba(255,255,255,0.5)">Planning</button>
          </div>
          <button class="btn btn-secondary"><i class="fas fa-plus"></i> Nouveau formulaire</button>
        </div>
      </div>

      <!-- Galerie -->
      <div id="formTab-galerie">
        <div class="filter-bar" style="margin-bottom:20px">
          <div class="filter-search"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Rechercher un formulaire..."></div>
          <select class="filter-select"><option>Tous les projets</option><option>AGRI-2025</option><option>Innov-Tech</option></select>
          <select class="filter-select"><option>Tous les statuts</option><option>Publié</option><option>Brouillon</option></select>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
          <div class="chart-card" style="cursor:pointer;transition:all 0.3s" onclick="switchFormTab('builder',null)" onmouseover="this.style.borderColor='rgba(46,175,125,0.4)'" onmouseout="this.style.borderColor='var(--border-dark)'">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
              <span class="badge badge-success">Publié</span>
              <span style="color:rgba(255,255,255,0.3);font-size:12px">12 nov. 2025</span>
            </div>
            <div style="color:#fff;font-weight:600;font-size:15px;margin-bottom:4px">Dossier de candidature AGRI</div>
            <div style="color:rgba(255,255,255,0.4);font-size:13px;margin-bottom:16px">Programme AGRI-2025</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
              <span class="badge badge-muted"><i class="fas fa-question-circle"></i> 12 champs</span>
              <span class="badge badge-muted"><i class="fas fa-users"></i> 47 soumissions</span>
            </div>
            <div style="display:flex;gap:6px">
              <button class="btn btn-secondary btn-sm" onclick="event.stopPropagation();switchFormTab('builder',null)"><i class="fas fa-edit"></i> Éditer</button>
              <button class="btn btn-ghost-dark btn-sm" onclick="event.stopPropagation()"><i class="fas fa-eye"></i></button>
              <button class="btn btn-ghost-dark btn-sm" onclick="event.stopPropagation()"><i class="fas fa-copy"></i></button>
              <button class="btn btn-sm" style="background:rgba(231,76,60,0.15);color:var(--danger)" onclick="event.stopPropagation();confirmDelete()"><i class="fas fa-trash"></i></button>
            </div>
          </div>
          <div class="chart-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px"><span class="badge badge-success">Publié</span><span style="color:rgba(255,255,255,0.3);font-size:12px">8 oct. 2025</span></div>
            <div style="color:#fff;font-weight:600;font-size:15px;margin-bottom:4px">Formulaire Innovation Tech</div>
            <div style="color:rgba(255,255,255,0.4);font-size:13px;margin-bottom:16px">Fonds Innovation Technologique</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px"><span class="badge badge-muted"><i class="fas fa-question-circle"></i> 9 champs</span><span class="badge badge-muted"><i class="fas fa-users"></i> 83 soumissions</span></div>
            <div style="display:flex;gap:6px"><button class="btn btn-ghost-dark btn-sm"><i class="fas fa-edit"></i> Éditer</button><button class="btn btn-ghost-dark btn-sm"><i class="fas fa-eye"></i></button></div>
          </div>
          <div class="chart-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px"><span class="badge badge-muted">Brouillon</span><span style="color:rgba(255,255,255,0.3);font-size:12px">2 déc. 2025</span></div>
            <div style="color:#fff;font-weight:600;font-size:15px;margin-bottom:4px">Candidature Bourses 2026</div>
            <div style="color:rgba(255,255,255,0.4);font-size:13px;margin-bottom:16px">Bourses d'Excellence 2026</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px"><span class="badge badge-muted"><i class="fas fa-question-circle"></i> 6 champs</span><span class="badge badge-muted"><i class="fas fa-users"></i> 0 soumissions</span></div>
            <div style="display:flex;gap:6px"><button class="btn btn-secondary btn-sm"><i class="fas fa-rocket"></i> Publier</button><button class="btn btn-ghost-dark btn-sm"><i class="fas fa-edit"></i></button></div>
          </div>
        </div>
      </div>

      <!-- Builder -->
      <div id="formTab-builder" class="hidden">
        <div class="canvas-toolbar">
          <button class="btn btn-ghost-dark btn-sm"><i class="fas fa-undo"></i></button>
          <button class="btn btn-ghost-dark btn-sm"><i class="fas fa-redo"></i></button>
          <div style="width:1px;height:24px;background:var(--border-dark)"></div>
          <button class="btn btn-ghost-dark btn-sm" onclick="$(this).toggleClass('active');$(this).css('color',$(this).hasClass('active')?'var(--secondary)':'')"><i class="fas fa-th"></i> Grille</button>
          <button class="btn btn-ghost-dark btn-sm active" style="color:var(--secondary)"><i class="fas fa-magnet"></i> Magnétisme</button>
          <div style="flex:1"></div>
          <button class="btn btn-ghost-dark btn-sm"><i class="fas fa-eye"></i> Aperçu</button>
          <button class="btn btn-ghost-dark btn-sm" onclick="showToast('Brouillon sauvegardé', 'success')"><i class="fas fa-save"></i> Brouillon</button>
          <button class="btn btn-secondary btn-sm" onclick="showToast('Formulaire publié avec succès !', 'success')"><i class="fas fa-rocket"></i> Publier</button>
        </div>
        <div class="form-builder-wrap">
          <div class="fb-sidebar">
            <div class="fb-section-title">TEXTE</div>
            <div class="fb-component"><i class="fas fa-heading"></i> Titre H1</div>
            <div class="fb-component"><i class="fas fa-heading"></i> Titre H2</div>
            <div class="fb-component"><i class="fas fa-paragraph"></i> Paragraphe</div>
            <div class="fb-section-title" style="margin-top:12px">CHAMPS</div>
            <div class="fb-component" draggable="true"><i class="fas fa-font"></i> Texte court</div>
            <div class="fb-component" draggable="true"><i class="fas fa-envelope"></i> Email</div>
            <div class="fb-component" draggable="true"><i class="fas fa-phone"></i> Téléphone</div>
            <div class="fb-component" draggable="true"><i class="fas fa-hashtag"></i> Numérique</div>
            <div class="fb-component" draggable="true"><i class="fas fa-calendar"></i> Date</div>
            <div class="fb-component" draggable="true"><i class="fas fa-align-left"></i> Texte long</div>
            <div class="fb-section-title" style="margin-top:12px">SÉLECTION</div>
            <div class="fb-component" draggable="true"><i class="fas fa-check-square"></i> Case à cocher</div>
            <div class="fb-component" draggable="true"><i class="fas fa-dot-circle"></i> Bouton radio</div>
            <div class="fb-component" draggable="true"><i class="fas fa-list"></i> Liste déroulante</div>
            <div class="fb-section-title" style="margin-top:12px">MÉDIAS</div>
            <div class="fb-component" draggable="true"><i class="fas fa-upload"></i> Upload fichier</div>
            <div class="fb-component" draggable="true"><i class="fas fa-image"></i> Upload image</div>
            <div class="fb-section-title" style="margin-top:12px">STRUCTURE</div>
            <div class="fb-component"><i class="fas fa-columns"></i> Grille 2 colonnes</div>
            <div class="fb-component"><i class="fas fa-grip-horizontal"></i> Séparateur</div>
          </div>
          <div class="fb-canvas" id="fbCanvas">
            <div class="fb-canvas-inner" id="fbCanvasInner">
              <div class="canvas-element selected" id="elem1">
                <div class="elem-label">Nom complet *</div>
                <div class="elem-input-mock">Entrez votre nom et prénom...</div>
                <div class="elem-controls"><button class="elem-btn" style="background:rgba(46,175,125,0.3);color:var(--secondary)"><i class="fas fa-edit"></i></button><button class="elem-btn" style="background:rgba(231,76,60,0.3);color:var(--danger)"><i class="fas fa-times"></i></button></div>
              </div>
              <div class="canvas-element" id="elem2">
                <div class="elem-label">Adresse email *</div>
                <div class="elem-input-mock"><i class="fas fa-envelope" style="margin-right:6px"></i> votre@email.com</div>
                <div class="elem-controls"><button class="elem-btn" style="background:rgba(46,175,125,0.3);color:var(--secondary)"><i class="fas fa-edit"></i></button><button class="elem-btn" style="background:rgba(231,76,60,0.3);color:var(--danger)"><i class="fas fa-times"></i></button></div>
              </div>
              <div class="canvas-element" id="elem3">
                <div class="elem-label">Description du projet *</div>
                <div class="elem-input-mock" style="height:70px;align-items:flex-start;padding-top:8px">Décrivez votre projet en détail...</div>
                <div class="elem-controls"><button class="elem-btn" style="background:rgba(46,175,125,0.3);color:var(--secondary)"><i class="fas fa-edit"></i></button><button class="elem-btn" style="background:rgba(231,76,60,0.3);color:var(--danger)"><i class="fas fa-times"></i></button></div>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="canvas-element" id="elem4">
                  <div class="elem-label">Pays</div>
                  <div class="elem-input-mock"><i class="fas fa-globe-africa" style="margin-right:6px"></i> Sélectionner...</div>
                  <div class="elem-controls"><button class="elem-btn" style="background:rgba(46,175,125,0.3);color:var(--secondary)"><i class="fas fa-edit"></i></button><button class="elem-btn" style="background:rgba(231,76,60,0.3);color:var(--danger)"><i class="fas fa-times"></i></button></div>
                </div>
                <div class="canvas-element" id="elem5">
                  <div class="elem-label">Budget demandé (FCFA)</div>
                  <div class="elem-input-mock">0</div>
                  <div class="elem-controls"><button class="elem-btn" style="background:rgba(46,175,125,0.3);color:var(--secondary)"><i class="fas fa-edit"></i></button><button class="elem-btn" style="background:rgba(231,76,60,0.3);color:var(--danger)"><i class="fas fa-times"></i></button></div>
                </div>
              </div>
                <input type="hidden" id="fb_layout_json" value="[]">
              <div class="canvas-element" id="elem6">
                <div class="elem-label">Document de présentation (PDF)</div>
                <div style="height:60px;background:rgba(255,255,255,0.03);border:2px dashed rgba(255,255,255,0.1);border-radius:6px;display:flex;align-items:center;justify-content:center;gap:8px;color:rgba(255,255,255,0.2);font-size:13px"><i class="fas fa-cloud-upload-alt"></i> Glissez ou cliquez pour uploader</div>
                <div class="elem-controls"><button class="elem-btn" style="background:rgba(46,175,125,0.3);color:var(--secondary)"><i class="fas fa-edit"></i></button><button class="elem-btn" style="background:rgba(231,76,60,0.3);color:var(--danger)"><i class="fas fa-times"></i></button></div>
              </div>
            </div>
          </div>
          <div class="fb-props">
            <div class="fb-section-title">PROPRIÉTÉS DE L'ÉLÉMENT</div>
            <div style="background:rgba(46,175,125,0.08);border:1px solid rgba(46,175,125,0.2);border-radius:8px;padding:10px;margin-bottom:16px;display:flex;align-items:center;gap:8px">
              <i class="fas fa-font" style="color:var(--secondary)"></i>
              <span style="color:var(--secondary);font-size:13px">Champ texte sélectionné</span>
            </div>
            <div style="margin-bottom:12px"><label style="color:rgba(255,255,255,0.5);font-size:11px;display:block;margin-bottom:6px">LIBELLÉ</label><input class="form-input-dark" style="width:100%" value="Nom complet"></div>
            <div style="margin-bottom:12px"><label style="color:rgba(255,255,255,0.5);font-size:11px;display:block;margin-bottom:6px">PLACEHOLDER</label><input class="form-input-dark" style="width:100%" value="Entrez votre nom et prénom..."></div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;padding:8px 0;border-bottom:1px solid var(--border-dark)"><span style="color:rgba(255,255,255,0.6);font-size:13px">Obligatoire</span><div class="toggle-switch on" onclick="$(this).toggleClass('on')"><div class="toggle-knob"></div></div></div>
            <div class="fb-section-title" style="margin-top:16px">STYLE</div>
            <div style="margin-bottom:12px"><label style="color:rgba(255,255,255,0.5);font-size:11px;display:block;margin-bottom:6px">LARGEUR</label><input type="range" min="25" max="100" value="100" style="width:100%;accent-color:var(--secondary)"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px">
              <div><label style="color:rgba(255,255,255,0.5);font-size:11px;display:block;margin-bottom:6px">TAILLE POLICE</label><input class="form-input-dark" style="width:100%" value="14" type="number"></div>
              <div><label style="color:rgba(255,255,255,0.5);font-size:11px;display:block;margin-bottom:6px">BORDURE (px)</label><input class="form-input-dark" style="width:100%" value="1" type="number"></div>
            </div>
            <div class="fb-section-title" style="margin-top:16px">VALIDATION</div>
            <div style="margin-bottom:12px"><label style="color:rgba(255,255,255,0.5);font-size:11px;display:block;margin-bottom:6px">LONGUEUR MIN</label><input class="form-input-dark" style="width:100%" value="3" type="number"></div>
            <div style="margin-bottom:12px"><label style="color:rgba(255,255,255,0.5);font-size:11px;display:block;margin-bottom:6px">MESSAGE D'ERREUR</label><input class="form-input-dark" style="width:100%" value="Ce champ est obligatoire"></div>
          </div>
        </div>
      </div>

      <!-- Planning -->
      <div id="formTab-planning" class="hidden">
        <div class="cal-wrap" id="calendarWrap"></div>
      </div>
    </div><!-- /formulaires -->

    <!-- ╔══ MODULE: FORMATION ══╗ -->
    <?php
      $trainingSessions = $trainingSessions ?? [];
      $trainingDashboard = $trainingDashboard ?? ['session' => null, 'participants' => [], 'stats' => []];
      $selectedTraining = $trainingDashboard['session'] ?? null;
      $trainingStats = $trainingDashboard['stats'] ?? [];
      $trainingParticipants = $trainingDashboard['participants'] ?? [];
      $trainingFormatLabels = ['hybride' => 'Hybride', 'presentiel' => 'Présentiel', 'en_ligne' => 'En ligne'];
      $trainingStatusLabel = !empty($selectedTraining['is_active']) ? 'Active' : 'Désactivée';
    ?>
    <div class="dash-module active" id="module-formation">
      <div class="module-header">
        <div><h1 class="module-title">Formation</h1><p class="module-sub">Sessions, présences, acquis et notes liées aux évaluations</p></div>
        <div class="module-actions">
          <select class="filter-select" id="trainingSessionSelect" onchange="selectTrainingSession(this.value)">
            <?php foreach ($trainingSessions as $training): ?><option value="<?= (int) $training['id'] ?>" <?= ((int) ($selectedTraining['id'] ?? 0) === (int) $training['id']) ? 'selected' : '' ?>><?= e($training['name']) ?><?= !empty($training['organization']) ? ' — ' . e($training['organization']) : '' ?></option><?php endforeach; ?>
          </select>
          <button class="btn btn-ghost-dark btn-sm" type="button" onclick="exportTrainingRegister()"><i class="fas fa-file-csv"></i> Exporter</button>
          <button class="btn btn-secondary" onclick="$('#modalTraining').addClass('open')"><i class="fas fa-plus"></i> Nouvelle formation</button>
        </div>
      </div>

      <div class="kpi-grid" style="margin-bottom:20px">
        <div class="kpi-card">
          <div class="kpi-card-top"><div class="kpi-icon" style="background:rgba(46,175,125,0.15);color:var(--secondary)"><i class="fas fa-chalkboard-teacher"></i></div><span class="badge <?= !empty($selectedTraining) && $selectedTraining['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $trainingStatusLabel ?></span></div>
          <div class="kpi-val" style="font-size:30px"><?= count($trainingSessions) ?></div><div class="kpi-label">Formations planifiées</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:<?= count($trainingSessions) ? 100 : 0 ?>%;background:var(--secondary)"></div></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-card-top"><div class="kpi-icon" style="background:rgba(245,166,35,0.15);color:var(--accent)"><i class="fas fa-user-check"></i></div><span class="kpi-trend up"><?= (int) ($trainingStats['registered'] ?? 0) ?>/<?= (int) ($selectedTraining['capacity'] ?? 0) ?></span></div>
          <div class="kpi-val" style="font-size:30px"><?= (int) ($trainingStats['registered'] ?? 0) ?></div><div class="kpi-label">Participants de l’organisation</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:<?= !empty($selectedTraining['capacity']) ? min(100, round(($trainingStats['registered'] / $selectedTraining['capacity']) * 100)) : 0 ?>%;background:var(--accent)"></div></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-card-top"><div class="kpi-icon" style="background:rgba(52,152,219,0.15);color:#3498db"><i class="fas fa-clipboard-check"></i></div><span class="badge badge-info">Présence</span></div>
          <div class="kpi-val" style="font-size:30px"><?= (int) ($trainingStats['attendance_rate'] ?? 0) ?>%</div><div class="kpi-label">Taux de présence</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:<?= (int) ($trainingStats['attendance_rate'] ?? 0) ?>%;background:#3498db"></div></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-card-top"><div class="kpi-icon" style="background:rgba(155,89,182,0.15);color:#9b59b6"><i class="fas fa-award"></i></div><span class="kpi-trend up"><?= (int) ($trainingStats['graded'] ?? 0) ?> notées</span></div>
          <div class="kpi-val" style="font-size:30px"><?= e((string) ($trainingStats['average'] ?? 0)) ?></div><div class="kpi-label">Moyenne des acquis /20</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:<?= min(100, (int) (($trainingStats['average'] ?? 0) * 5)) ?>%;background:#9b59b6"></div></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1.15fr 0.85fr;gap:20px;margin-bottom:20px">
        <div class="table-card">
          <div class="chart-card-header" style="margin-bottom:16px">
            <div><div class="chart-title">Catalogue des formations</div><div class="chart-sub">Chaque session est rattachée à une organisation</div></div>
          </div>
          <table class="dash-table">
            <thead><tr><th>Formation</th><th>Projet</th><th>Dates</th><th>Participants</th><th>Statut</th><th>Action</th></tr></thead>
            <tbody><?php foreach ($trainingSessions as $training): ?><tr>
              <td><div style="font-weight:600;color:#fff"><?= e($training['name']) ?></div><div style="color:rgba(255,255,255,.35);font-size:12px"><?= e($trainingFormatLabels[$training['format']] ?? $training['format']) ?> · <?= e($training['objective']) ?></div></td>
              <td><?= e($training['organization'] ?? '—') ?></td><td><?= e($training['session_date']) ?></td><td><?= ((int) $training['id'] === (int) ($selectedTraining['id'] ?? 0)) ? (int) ($trainingStats['registered'] ?? 0) : '—' ?> / <?= (int) $training['capacity'] ?></td>
              <td><span class="badge <?= !empty($training['is_active']) ? 'badge-success' : 'badge-muted' ?>"><?= !empty($training['is_active']) ? 'Active' : 'Désactivée' ?></span></td>
              <td><button class="btn btn-ghost-dark btn-sm" type="button" onclick="selectTrainingSession(<?= (int) $training['id'] ?>)"><i class="fas fa-eye"></i></button></td>
            </tr><?php endforeach; ?><?php if (!$trainingSessions): ?><tr><td colspan="6">Aucune formation enregistrée.</td></tr><?php endif; ?></tbody>
          </table>
        </div>

        <div class="chart-card" id="trainingDetailPanel">
          <div class="chart-card-header" style="margin-bottom:16px">
            <div><div class="chart-title">Pilotage de la session</div><div class="chart-sub"><?= e($selectedTraining['organization'] ?? 'Aucune organisation') ?></div></div>
            <span class="badge <?= !empty($selectedTraining['is_active']) ? 'badge-success' : 'badge-muted' ?>"><i class="fas fa-circle" style="font-size:8px"></i> <?= $trainingStatusLabel ?></span>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
            <div style="background:rgba(255,255,255,.04);border:1px solid var(--border-dark);border-radius:10px;padding:12px"><div style="color:rgba(255,255,255,.35);font-size:12px">Présence</div><div style="color:#fff;font-size:22px;font-weight:700"><?= (int) ($trainingStats['attendance_rate'] ?? 0) ?>%</div></div>
            <div style="background:rgba(255,255,255,.04);border:1px solid var(--border-dark);border-radius:10px;padding:12px"><div style="color:rgba(255,255,255,.35);font-size:12px">Poids optionnel</div><div style="color:#fff;font-size:22px;font-weight:700"><?= e((string) ($selectedTraining['evaluation_weight'] ?? 0)) ?>%</div></div>
            <div style="background:rgba(255,255,255,.04);border:1px solid var(--border-dark);border-radius:10px;padding:12px"><div style="color:rgba(255,255,255,.35);font-size:12px">Présents</div><div style="color:#fff;font-size:22px;font-weight:700"><?= (int) ($trainingStats['present'] ?? 0) ?></div></div>
            <div style="background:rgba(255,255,255,.04);border:1px solid var(--border-dark);border-radius:10px;padding:12px"><div style="color:rgba(255,255,255,.35);font-size:12px">Moyenne</div><div style="color:#fff;font-size:22px;font-weight:700"><?= e((string) ($trainingStats['average'] ?? 0)) ?></div></div>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-top:1px solid var(--border-dark);border-bottom:1px solid var(--border-dark);margin-bottom:14px">
            <div><div style="color:#fff;font-weight:600">Notes incluses dans les évaluations</div><div style="color:rgba(255,255,255,.38);font-size:12px">Configuration de la session sélectionnée.</div></div>
            <span class="badge <?= (float) ($selectedTraining['evaluation_weight'] ?? 0) > 0 ? 'badge-success' : 'badge-muted' ?>"><?= (float) ($selectedTraining['evaluation_weight'] ?? 0) > 0 ? 'Oui' : 'Non' ?></span>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="btn btn-secondary btn-sm" type="button" onclick="saveAllTrainingParticipants()"><i class="fas fa-save"></i> Enregistrer les changements</button>
            <button class="btn btn-ghost-dark btn-sm" type="button" onclick="switchModule('evaluations')"><i class="fas fa-star"></i> Voir impact</button>
          </div>
        </div>
      </div>

      <div class="table-card">
        <div class="chart-card-header" style="margin-bottom:16px">
          <div><div class="chart-title">Présences et notes des candidats</div><div class="chart-sub">Candidatures de <?= e($selectedTraining['organization'] ?? 'l’organisation sélectionnée') ?></div></div>
        </div>
        <table class="dash-table">
          <thead><tr><th>Candidat</th><th>Projet</th><th>Présence</th><th>Note /20</th><th>Appréciation</th><th>Inclure</th><th>Statut</th></tr></thead>
          <tbody><?php foreach ($trainingParticipants as $participant): ?><tr data-submission-id="<?= (int) $participant['submission_id'] ?>">
            <td><?= e($participant['candidate_name'] ?: $participant['candidate_email']) ?></td><td><?= e($selectedTraining['organization'] ?? '—') ?></td><td><select class="filter-select training-attendance"><option value="registered" <?= $participant['attendance_status'] === 'registered' ? 'selected' : '' ?>>Inscrit</option><option value="present" <?= $participant['attendance_status'] === 'present' ? 'selected' : '' ?>>Présent</option><option value="absent" <?= $participant['attendance_status'] === 'absent' ? 'selected' : '' ?>>Absent</option><option value="certified" <?= $participant['attendance_status'] === 'certified' ? 'selected' : '' ?>>Certifié</option></select></td><td><input class="form-input-dark training-grade" value="<?= e((string) ($participant['grade'] ?? '')) ?>" type="number" min="0" max="20" step="0.5" style="width:70px"></td><td><input class="form-input-dark training-comment" value="<?= e((string) ($participant['comment'] ?? '')) ?>" placeholder="Commentaire" style="width:100%"></td><td><input type="checkbox" class="training-included" <?= !empty($participant['included_in_evaluation']) ? 'checked' : '' ?>></td><td><span class="badge <?= $participant['attendance_status'] === 'certified' ? 'badge-success' : 'badge-muted' ?>"><?= e(ucfirst($participant['attendance_status'])) ?></span></td>
          </tr><?php endforeach; ?><?php if (!$trainingParticipants): ?><tr><td colspan="7">Aucune candidature liée à l’organisation de cette formation.</td></tr><?php endif; ?></tbody>
        </table>
      </div>
    </div><!-- /formation -->

    <!-- ╔══ MODULE: ÉVALUATIONS ══╗ -->
    <div class="dash-module" id="module-evaluations">
      <div class="module-header">
        <div><h1 class="module-title">Évaluation des candidatures</h1><p class="module-sub">Notez chaque critère avec précision</p></div>
        <div class="module-actions">
          <select class="filter-select"><option>Programme AGRI-2025</option><option>Innov-Tech</option></select>
          <button class="btn btn-ghost-dark btn-sm" onclick="switchModule('classements')"><i class="fas fa-trophy"></i> Voir classements</button>
        </div>
      </div>

      <div style="background:rgba(46,175,125,0.08);border:1px solid rgba(46,175,125,0.2);border-radius:10px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px">
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:36px;height:36px;border-radius:10px;background:rgba(46,175,125,0.15);color:var(--secondary);display:flex;align-items:center;justify-content:center"><i class="fas fa-chalkboard-teacher"></i></div>
          <div><div style="color:#fff;font-weight:600">Notes de formation disponibles</div><div style="color:rgba(255,255,255,0.42);font-size:13px">4 sessions peuvent alimenter l'évaluation comme critère optionnel, selon le paramétrage de chaque formation.</div></div>
        </div>
        <button class="btn btn-ghost-dark btn-sm" onclick="switchModule('formation')"><i class="fas fa-sliders-h"></i> Gérer</button>
      </div>

      <!-- Submissions list -->
      <div class="table-card" style="margin-bottom:20px">
        <div class="chart-card-header" style="margin-bottom:16px">
          <div><div class="chart-title">Candidatures en attente d'évaluation</div><div class="chart-sub">44 candidatures à traiter</div></div>
          <div style="display:flex;gap:8px">
            <select class="filter-select"><option>Tous les statuts</option><option>En attente</option><option>En révision</option></select>
          </div>
        </div>
        <table class="dash-table">
          <thead><tr><th>#Réf</th><th>Candidat</th><th>Pays</th><th>Projet</th><th>Soumis le</th><th>Statut</th><th>Action</th></tr></thead>
          <tbody>
            <tr style="cursor:pointer" onclick="showEvalForm()"><td class="font-mono" style="color:var(--secondary)">AGRI-047</td><td><div class="candidate-name"><div class="candidate-av" style="background:var(--secondary)">AK</div> Amara Konaté</div></td><td>🇨🇮 Côte d'Ivoire</td><td>AGRI-2025</td><td>12 nov. 2025</td><td><span class="badge badge-warning">En révision</span></td><td><button class="btn btn-secondary btn-sm" onclick="event.stopPropagation();showEvalForm()"><i class="fas fa-star"></i> Évaluer</button></td></tr>
            <tr><td class="font-mono" style="color:var(--secondary)">AGRI-046</td><td><div class="candidate-name"><div class="candidate-av" style="background:#e67e22">JN</div> Jean Ngoma</div></td><td>🇨🇲 Cameroun</td><td>AGRI-2025</td><td>11 nov. 2025</td><td><span class="badge badge-info">Soumis</span></td><td><button class="btn btn-ghost-dark btn-sm"><i class="fas fa-star"></i> Évaluer</button></td></tr>
            <tr><td class="font-mono" style="color:var(--secondary)">AGRI-045</td><td><div class="candidate-name"><div class="candidate-av" style="background:#9b59b6">OB</div> Omar Ba</div></td><td>🇬🇳 Guinée</td><td>AGRI-2025</td><td>10 nov. 2025</td><td><span class="badge badge-info">Soumis</span></td><td><button class="btn btn-ghost-dark btn-sm"><i class="fas fa-star"></i> Évaluer</button></td></tr>
          </tbody>
        </table>
      </div>

      <!-- Eval Form -->
      <div id="evalFormSection" class="hidden">
        <div class="eval-layout">
          <div class="eval-candidate-view">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border-dark)">
              <div class="candidate-av" style="width:48px;height:48px;font-size:18px;background:var(--secondary)">AK</div>
              <div><div style="color:#fff;font-weight:600;font-size:16px">Amara Konaté</div><div style="color:rgba(255,255,255,0.4);font-size:13px">amara.konate@email.ci — 🇨🇮 Côte d'Ivoire</div><div style="color:rgba(255,255,255,0.3);font-size:12px;margin-top:2px">Réf: AGRI-047 · Soumis le 12 nov. 2025</div></div>
            </div>
            <div style="margin-bottom:20px"><div style="color:rgba(255,255,255,0.5);font-size:12px;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px">Description du projet</div><div style="color:rgba(255,255,255,0.8);font-size:14px;line-height:1.7;background:rgba(255,255,255,0.03);padding:14px;border-radius:8px;border:1px solid var(--border-dark)">Notre projet vise à développer un système d'irrigation solaire pour les petits agriculteurs de la région de Korhogo. Nous prévoyons d'équiper 200 exploitations sur 24 mois, avec un impact estimé de 1500 familles bénéficiaires.</div></div>
            <div style="margin-bottom:20px"><div style="color:rgba(255,255,255,0.5);font-size:12px;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px">Budget demandé</div><div style="color:var(--secondary);font-family:'JetBrains Mono',monospace;font-size:22px;font-weight:700">45 000 000 FCFA</div></div>
            <div><div style="color:rgba(255,255,255,0.5);font-size:12px;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px">Pays d'implémentation</div><div style="color:rgba(255,255,255,0.8)">🇨🇮 Côte d'Ivoire — Région du Poro</div></div>
          </div>
          <div class="eval-scoring">
            <div class="eval-scoring-header">
              <div style="text-align:center;padding:16px 0">
                <svg class="score-ring-big" viewBox="0 0 140 140" id="evalScoreRing">
                  <circle cx="70" cy="70" r="56" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="10"/>
                  <circle cx="70" cy="70" r="56" fill="none" stroke="url(#ringGrad)" stroke-width="10" stroke-linecap="round" stroke-dasharray="351.9" stroke-dashoffset="176" transform="rotate(-90 70 70)" id="evalRingCircle"/>
                  <text x="70" y="62" text-anchor="middle" fill="#fff" font-size="28" font-weight="700" font-family="Poppins" id="evalScoreText">0</text>
                  <text x="70" y="78" text-anchor="middle" fill="rgba(255,255,255,0.4)" font-size="11">/20 pts</text>
                  <text x="70" y="95" text-anchor="middle" fill="var(--secondary)" font-size="10" id="evalLevelText">—</text>
                </svg>
                <div style="color:rgba(255,255,255,0.5);font-size:12px">Score pondéré en temps réel</div>
              </div>
            </div>
            <div id="criteriaScoring">
              <div class="criteria-score-item">
                <div class="criteria-score-label">Pertinence du projet <span class="criteria-score-max">×2.0 — /20</span></div>
                <div class="slider-wrap">
                  <div class="score-slider-track" onclick="handleSliderClick(event, this, 'score1')"><div class="score-slider-fill" style="width:70%" id="fill-score1"></div><div class="score-slider-thumb" style="left:calc(70% - 9px)"></div></div>
                  <input type="number" class="score-input-mini" id="score1" value="14" min="0" max="20" step="0.5" oninput="updateEvalScore()">
                </div>
                <textarea style="width:100%;margin-top:8px;background:rgba(255,255,255,0.04);border:1px solid var(--border-dark);border-radius:6px;padding:8px;color:rgba(255,255,255,0.6);font-size:12px;resize:none;height:50px;outline:none" placeholder="Commentaire (optionnel)..."></textarea>
              </div>
              <div class="criteria-score-item">
                <div class="criteria-score-label">Faisabilité technique <span class="criteria-score-max">×1.5 — /20</span></div>
                <div class="slider-wrap">
                  <div class="score-slider-track" onclick="handleSliderClick(event, this, 'score2')"><div class="score-slider-fill" style="width:55%" id="fill-score2"></div><div class="score-slider-thumb" style="left:calc(55% - 9px)"></div></div>
                  <input type="number" class="score-input-mini" id="score2" value="11" min="0" max="20" step="0.5" oninput="updateEvalScore()">
                </div>
              </div>
              <div class="criteria-score-item">
                <div class="criteria-score-label">Impact environnemental <span class="criteria-score-max">×1.5 — /20</span></div>
                <div class="slider-wrap">
                  <div class="score-slider-track" onclick="handleSliderClick(event, this, 'score3')"><div class="score-slider-fill" style="width:80%" id="fill-score3"></div><div class="score-slider-thumb" style="left:calc(80% - 9px)"></div></div>
                  <input type="number" class="score-input-mini" id="score3" value="16" min="0" max="20" step="0.5" oninput="updateEvalScore()">
                </div>
              </div>
              <div class="criteria-score-item">
                <div class="criteria-score-label">Plan financier <span class="criteria-score-max">×1.0 — /20</span></div>
                <div class="slider-wrap">
                  <div class="score-slider-track" onclick="handleSliderClick(event, this, 'score4')"><div class="score-slider-fill" style="width:60%" id="fill-score4"></div><div class="score-slider-thumb" style="left:calc(60% - 9px)"></div></div>
                  <input type="number" class="score-input-mini" id="score4" value="12" min="0" max="20" step="0.5" oninput="updateEvalScore()">
                </div>
              </div>
            </div>
            <div style="padding:16px">
              <button class="btn btn-secondary" style="width:100%;justify-content:center" onclick="showToast('Évaluation enregistrée avec succès !', 'success');$('#evalFormSection').addClass('hidden')">
                <i class="fas fa-check"></i> Enregistrer l'évaluation
              </button>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /evaluations -->

    <!-- ╔══ MODULE: CLASSEMENTS ══╗ -->
    <div class="dash-module" id="module-classements">
      <div class="module-header">
        <div><h1 class="module-title">Classements officiels</h1><p class="module-sub">Programme AGRI-2025 — 47 candidats</p></div>
        <div class="module-actions">
          <select class="filter-select"><option>Programme AGRI-2025</option><option>Innov-Tech</option></select>
          <button class="btn btn-ghost-dark btn-sm" onclick="showToast('Export CSV téléchargé', 'success')"><i class="fas fa-file-csv"></i> CSV</button>
          <button class="btn btn-secondary btn-sm" onclick="showToast('Rapport PDF généré', 'success')"><i class="fas fa-file-pdf"></i> Rapport PDF</button>
        </div>
      </div>
      <div class="table-card">
        <table class="rank-table">
          <thead><tr><th>Rang</th><th>Candidat</th><th>Pays</th><th>Score pondéré</th><th>Niveau</th><th>Statut</th><th>Actions</th></tr></thead>
          <tbody>
            <tr><td><span class="rank-medal">🥇</span><span class="font-mono" style="font-size:12px;color:rgba(255,255,255,0.3)"> 1</span></td><td><div class="candidate-name"><div class="candidate-av" style="background:#9b59b6">MS</div><div><div>Mariama Sy</div><div style="font-size:12px;color:rgba(255,255,255,0.35)">mariama.sy@email.ml</div></div></div></td><td>🇲🇱 Mali</td><td><div><div class="font-mono" style="color:var(--secondary);font-size:16px;font-weight:700">18.2</div><div class="rank-score-bar"><div class="rank-score-fill" style="width:91%"></div></div></div></td><td><span class="badge badge-success">Excellent</span></td><td><span class="badge badge-success"><i class="fas fa-globe"></i> Publié</span></td><td><div style="display:flex;gap:4px"><button class="btn btn-icon btn-ghost-dark" title="Voir dossier"><i class="fas fa-eye"></i></button><button class="btn btn-icon" style="background:rgba(231,76,60,0.1);color:var(--danger)" title="Dépublier"><i class="fas fa-eye-slash"></i></button></div></td></tr>
            <tr><td><span class="rank-medal">🥈</span><span class="font-mono" style="font-size:12px;color:rgba(255,255,255,0.3)"> 2</span></td><td><div class="candidate-name"><div class="candidate-av" style="background:var(--primary)">FD</div><div><div>Fatima Diallo</div><div style="font-size:12px;color:rgba(255,255,255,0.35)">f.diallo@email.sn</div></div></div></td><td>🇸🇳 Sénégal</td><td><div><div class="font-mono" style="color:var(--accent);font-size:16px;font-weight:700">16.4</div><div class="rank-score-bar"><div class="rank-score-fill" style="width:82%;background:linear-gradient(90deg,var(--accent),#f7c55a)"></div></div></div></td><td><span class="badge badge-success">Très bien</span></td><td><span class="badge badge-success"><i class="fas fa-globe"></i> Publié</span></td><td><div style="display:flex;gap:4px"><button class="btn btn-icon btn-ghost-dark"><i class="fas fa-eye"></i></button><button class="btn btn-icon" style="background:rgba(231,76,60,0.1);color:var(--danger)"><i class="fas fa-eye-slash"></i></button></div></td></tr>
            <tr><td><span class="rank-medal">🥉</span><span class="font-mono" style="font-size:12px;color:rgba(255,255,255,0.3)"> 3</span></td><td><div class="candidate-name"><div class="candidate-av" style="background:#e67e22">KO</div><div><div>K. Ouédraogo</div><div style="font-size:12px;color:rgba(255,255,255,0.35)">k.ouedraogo@bf.net</div></div></div></td><td>🇧🇫 Burkina</td><td><div><div class="font-mono" style="color:#e67e22;font-size:16px;font-weight:700">15.8</div><div class="rank-score-bar"><div class="rank-score-fill" style="width:79%;background:linear-gradient(90deg,#e67e22,#f0a35a)"></div></div></div></td><td><span class="badge badge-info">Bien</span></td><td><span class="badge badge-muted"><i class="fas fa-eye-slash"></i> Non publié</span></td><td><div style="display:flex;gap:4px"><button class="btn btn-icon btn-ghost-dark"><i class="fas fa-eye"></i></button><button class="btn btn-secondary btn-sm"><i class="fas fa-globe"></i> Publier</button></div></td></tr>
            <tr><td><span style="color:rgba(255,255,255,0.3);font-weight:600;padding-left:8px">4</span></td><td><div class="candidate-name"><div class="candidate-av" style="background:#3498db">IT</div><div><div>Ibrahim Touré</div></div></div></td><td>🇬🇳 Guinée</td><td><div><div class="font-mono" style="color:rgba(255,255,255,0.7);font-size:16px">14.9</div><div class="rank-score-bar"><div class="rank-score-fill" style="width:74.5%;background:linear-gradient(90deg,#3498db,#5dade2)"></div></div></div></td><td><span class="badge badge-info">Bien</span></td><td><span class="badge badge-muted">Non publié</span></td><td><div style="display:flex;gap:4px"><button class="btn btn-icon btn-ghost-dark"><i class="fas fa-eye"></i></button><button class="btn btn-secondary btn-sm"><i class="fas fa-globe"></i> Publier</button></div></td></tr>
            <tr><td><span style="color:rgba(255,255,255,0.3);font-weight:600;padding-left:8px">5</span></td><td><div class="candidate-name"><div class="candidate-av" style="background:#27ae60">AC</div><div><div>A. Coulibaly</div></div></div></td><td>🇨🇮 Côte d'Ivoire</td><td><div><div class="font-mono" style="color:rgba(255,255,255,0.7);font-size:16px">13.5</div><div class="rank-score-bar"><div class="rank-score-fill" style="width:67.5%;background:linear-gradient(90deg,#27ae60,#58d68d)"></div></div></div></td><td><span class="badge badge-warning">Passable</span></td><td><span class="badge badge-muted">Non publié</span></td><td><button class="btn btn-ghost-dark btn-sm"><i class="fas fa-eye"></i></button></td></tr>
          </tbody>
        </table>
      </div>

      <!-- Eval Levels -->
      <div style="margin-top:24px"><div class="module-title" style="font-size:18px;margin-bottom:16px">Niveaux d'évaluation définis</div></div>
      <div class="levels-grid">
        <div class="level-card" style="border-left-color:var(--secondary)"><div class="level-range">17.0 — 20.0</div><div class="level-name" style="color:var(--secondary)">Excellent</div><div class="level-desc">Projet exemplaire. Réunit toutes les conditions de financement.</div></div>
        <div class="level-card" style="border-left-color:#3498db"><div class="level-range">14.0 — 16.9</div><div class="level-name" style="color:#3498db">Très bien</div><div class="level-desc">Projet solide avec quelques points à renforcer.</div></div>
        <div class="level-card" style="border-left-color:var(--accent)"><div class="level-range">11.0 — 13.9</div><div class="level-name" style="color:var(--accent)">Bien</div><div class="level-desc">Bonne proposition mais nécessite des améliorations ciblées.</div></div>
        <div class="level-card" style="border-left-color:#e67e22"><div class="level-range">8.0 — 10.9</div><div class="level-name" style="color:#e67e22">Passable</div><div class="level-desc">Projet à potentiel, mais insuffisant en l'état actuel.</div></div>
        <div class="level-card" style="border-left-color:var(--danger)"><div class="level-range">0.0 — 7.9</div><div class="level-name" style="color:var(--danger)">Insuffisant</div><div class="level-desc">Ne répond pas aux critères minimaux du programme.</div></div>
        <div class="level-card" style="border:2px dashed rgba(46,175,125,0.3);cursor:pointer;display:flex;align-items:center;justify-content:center" onclick="showToast('Formulaire niveau ouvert', 'success')"><div style="text-align:center"><i class="fas fa-plus" style="font-size:24px;color:var(--secondary);display:block;margin-bottom:8px"></i><span style="color:var(--secondary);font-weight:600">Ajouter un niveau</span></div></div>
      </div>
    </div><!-- /classements -->

    <!-- ╔══ MODULE: CALENDRIER ══╗ -->
    <div class="dash-module" id="module-calendrier">
      <div class="module-header">
        <div><h1 class="module-title">Planning des formulaires</h1><p class="module-sub">Gérez les périodes d'ouverture des appels à candidatures</p></div>
        <div class="module-actions">
          <button class="btn btn-secondary" onclick="$('#modalPlanning').addClass('open')"><i class="fas fa-plus"></i> Planifier un formulaire</button>
        </div>
      </div>
      <div class="cal-wrap">
        <div id="adminCalendar"></div>
      </div>
    </div><!-- /calendrier -->

    <!-- ╔══ MODULE: PARAMÈTRES ══╗ -->
    <div class="dash-module" id="module-parametres">
      <div class="module-header">
        <div><h1 class="module-title">Paramètres système</h1><p class="module-sub">Configuration globale de Criteval_pro</p></div>
        <div class="module-actions"><button class="btn btn-secondary" onclick="showToast('Paramètres enregistrés', 'success')"><i class="fas fa-save"></i> Enregistrer</button></div>
      </div>
      <form id="settingsForm" method="post" action="<?= BASE_URL ?>/admin/settings">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="settings-grid">
          <div class="settings-nav">
            <div class="settings-nav-item active" data-settings-panel="general" onclick="switchSettingsPanel('general', this)"><i class="fas fa-info-circle"></i> Général</div>
            <div class="settings-nav-item" data-settings-panel="modules" onclick="switchSettingsPanel('modules', this)"><i class="fas fa-puzzle-piece"></i> Modules</div>
            <div class="settings-nav-item" data-settings-panel="users" onclick="switchSettingsPanel('users', this)"><i class="fas fa-users"></i> Utilisateurs</div>
            <div class="settings-nav-item" data-settings-panel="email" onclick="switchSettingsPanel('email', this)"><i class="fas fa-envelope"></i> Email SMTP</div>
            <div class="settings-nav-item" data-settings-panel="security" onclick="switchSettingsPanel('security', this)"><i class="fas fa-shield-alt"></i> Sécurité</div>
          </div>
          <div class="settings-panel">
            <div class="settings-section settings-pane active" id="settings-general">
              <div class="settings-section-title"><i class="fas fa-building" style="color:var(--secondary)"></i> Informations de l'organisation</div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Nom de l'application</label><input name="application_name" class="form-input-dark" style="width:100%" value="<?= e($appSettings['application_name'] ?? 'Criteval Pro') ?>"></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Organisation</label><input name="organization" class="form-input-dark" style="width:100%" value="<?= e($appSettings['organization'] ?? '') ?>"></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Email contact</label><input name="contact_email" class="form-input-dark" style="width:100%" value="<?= e($appSettings['contact_email'] ?? '') ?>"></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Site web</label><input name="website" class="form-input-dark" style="width:100%" value="<?= e($appSettings['website'] ?? '') ?>"></div>
              </div>
            </div>
            <div class="settings-section settings-pane hidden" id="settings-modules">
              <div class="settings-section-title"><i class="fas fa-puzzle-piece" style="color:var(--accent)"></i> Modules actifs</div>
              <?php foreach (($appSettings['modules'] ?? []) as $moduleKey => $enabled): ?>
                <div class="settings-row"><div><div class="settings-label"><?= e($modulePermissionLabels[$moduleKey] ?? ucfirst((string) $moduleKey)) ?></div><div class="settings-desc">Afficher ce module dans l'administration.</div></div><input type="hidden" name="modules[<?= e((string) $moduleKey) ?>]" value="0"><label class="toggle-switch <?= $enabled ? 'on' : '' ?>"><input type="checkbox" name="modules[<?= e((string) $moduleKey) ?>]" value="1" <?= $enabled ? 'checked' : '' ?> hidden><div class="toggle-knob"></div></label></div>
              <?php endforeach; ?>
            </div>
            <div class="settings-section settings-pane hidden" id="settings-users">
              <div class="settings-section-title"><i class="fas fa-users" style="color:var(--secondary)"></i> Utilisateurs</div>
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><span class="settings-desc"><?= count($users) ?> compte(s) enregistré(s)</span><button class="btn btn-secondary btn-sm" type="button" onclick="openDashboardUserModal()"><i class="fas fa-user-plus"></i> Nouvel utilisateur</button></div>
              <div style="overflow:auto"><table class="rank-table"><thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th></th></tr></thead><tbody>
                <?php foreach ($users as $user): ?>
                  <tr><td><?= e($user['name'] ?? '') ?></td><td><?= e($user['email'] ?? '') ?></td><td><?= e(role_label($user['role'] ?? 'user')) ?></td><td><span class="badge <?= !empty($user['is_active']) ? 'badge-success' : 'badge-muted' ?>"><?= !empty($user['is_active']) ? 'Actif' : 'Inactif' ?></span></td><td><button type="button" class="btn btn-ghost-dark btn-sm" onclick='openDashboardUserModal(<?= json_encode($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button> <button type="button" class="btn btn-sm" style="background:rgba(231,76,60,0.15);color:var(--danger)" onclick="deleteDashboardUser(<?= (int) ($user['id'] ?? 0) ?>)"><i class="fas fa-trash"></i></button></td></tr>
                <?php endforeach; ?>
              </tbody></table></div>
            </div>
            <div class="settings-section settings-pane hidden" id="settings-email">
              <div class="settings-section-title"><i class="fas fa-envelope" style="color:var(--secondary)"></i> Configuration SMTP</div>
              <div id="smtpConnectivityStatus" class="settings-desc" style="margin-bottom:12px">Vérification du statut email...</div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Mode d'envoi</label><select name="smtp[transport]" class="form-input-dark" style="width:100%"><option value="auto" <?= ($appSettings['smtp']['transport'] ?? 'auto') === 'auto' ? 'selected' : '' ?>>Auto recommandé</option><option value="smtp" <?= ($appSettings['smtp']['transport'] ?? 'auto') === 'smtp' ? 'selected' : '' ?>>SMTP uniquement</option><option value="php" <?= ($appSettings['smtp']['transport'] ?? 'auto') === 'php' ? 'selected' : '' ?>>PHP mail()</option></select></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Serveur SMTP</label><select id="smtpProfile" name="smtp[profile]" class="form-input-dark" style="width:100%"><option value="hostinger" <?= ($appSettings['smtp']['profile'] ?? (($appSettings['smtp']['host'] ?? '') === '127.0.0.1' ? 'localhost' : 'hostinger')) === 'hostinger' ? 'selected' : '' ?>>Hostinger</option><option value="localhost" <?= ($appSettings['smtp']['profile'] ?? '') === 'localhost' || (($appSettings['smtp']['profile'] ?? '') === '' && ($appSettings['smtp']['host'] ?? '') === '127.0.0.1') ? 'selected' : '' ?>>Localhost</option><option value="custom" <?= ($appSettings['smtp']['profile'] ?? '') === 'custom' ? 'selected' : '' ?>>Personnalisé</option></select></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Hôte SMTP</label><input id="smtpHost" name="smtp[host]" class="form-input-dark" style="width:100%" value="<?= e($appSettings['smtp']['host'] ?? '') ?>"></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Port</label><input name="smtp[port]" class="form-input-dark" style="width:100%" value="<?= e((string) ($appSettings['smtp']['port'] ?? 587)) ?>"></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Utilisateur</label><input name="smtp[username]" class="form-input-dark" style="width:100%" value="<?= e($appSettings['smtp']['username'] ?? '') ?>"></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Mot de passe</label><input type="password" name="smtp[password]" class="form-input-dark" style="width:100%" placeholder="Laisser vide pour conserver"></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Sécurité</label><select name="smtp[security]" class="form-input-dark" style="width:100%"><option value="tls" <?= ($appSettings['smtp']['security'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option><option value="ssl" <?= ($appSettings['smtp']['security'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option><option value="none" <?= ($appSettings['smtp']['security'] ?? '') === 'none' ? 'selected' : '' ?>>Aucune</option></select></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Expéditeur</label><input name="smtp[from]" class="form-input-dark" style="width:100%" value="<?= e($appSettings['smtp']['from'] ?? '') ?>"></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Nom expéditeur</label><input name="smtp[sender]" class="form-input-dark" style="width:100%" value="<?= e($appSettings['smtp']['sender'] ?? 'Criteval Pro') ?>"></div>
                <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:6px;display:block">Timeout</label><input type="number" name="smtp[timeout]" class="form-input-dark" style="width:100%" value="<?= e((string) ($appSettings['smtp']['timeout'] ?? 15)) ?>"></div>
              </div>
              <div style="margin-top:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap"><button id="smtpTestBtn" class="btn btn-ghost-dark btn-sm" type="button"><i class="fas fa-paper-plane"></i> Tester la connexion</button><span id="smtpTestStatus" class="settings-desc"></span></div>
            </div>
            <div class="settings-section settings-pane hidden" id="settings-security">
              <div class="settings-section-title"><i class="fas fa-shield-alt" style="color:var(--accent)"></i> Sécurité & Base de données</div>
              <div class="settings-row"><div><div class="settings-label">Durée de session</div><div class="settings-desc">Déconnexion automatique après inactivité</div></div><div style="display:flex;align-items:center;gap:8px"><input type="number" name="security[session_timeout]" class="form-input-dark" value="<?= e((string) ($appSettings['security']['session_timeout'] ?? 60)) ?>" style="width:70px"> <span style="color:rgba(255,255,255,0.4)">minutes</span></div></div>
              <div class="settings-row"><div><div class="settings-label">Tentatives OTP max</div><div class="settings-desc">Blocage après N tentatives échouées</div></div><input type="number" name="security[otp_attempts]" class="form-input-dark" value="<?= e((string) ($appSettings['security']['otp_attempts'] ?? 3)) ?>" style="width:70px"></div>
              <?php foreach (['logs_enabled' => "Logs d'accès", 'two_factor_enabled' => 'Authentification 2FA', 'csrf_protection' => 'Protection CSRF'] as $key => $label): ?>
                <div class="settings-row"><div><div class="settings-label"><?= e($label) ?></div><div class="settings-desc">Paramètre de sécurité global.</div></div><input type="hidden" name="security[<?= e($key) ?>]" value="0"><label class="toggle-switch <?= !empty($appSettings['security'][$key]) ? 'on' : '' ?>"><input type="checkbox" name="security[<?= e($key) ?>]" value="1" <?= !empty($appSettings['security'][$key]) ? 'checked' : '' ?> hidden><div class="toggle-knob"></div></label></div>
              <?php endforeach; ?>
              <?php if ($databaseInfo): ?>
                <div style="margin-top:18px;padding-top:18px;border-top:1px solid var(--border-dark)">
                  <div class="settings-section-title"><i class="fas fa-database" style="color:var(--secondary)"></i> Base de données</div>
                  <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;color:rgba(255,255,255,0.7);font-size:13px">
                    <?php foreach (['status' => 'Statut', 'type' => 'Type', 'name' => 'Nom', 'host' => 'Hôte', 'port' => 'Port', 'tables' => 'Tables', 'server' => 'Version', 'size' => 'Taille', 'modified' => 'Modifiée'] as $key => $label): ?>
                      <div><strong><?= e($label) ?>:</strong> <?= e((string) ($databaseInfo[$key] ?? '—')) ?></div>
                    <?php endforeach; ?>
                    <div style="grid-column:1/-1"><strong>Chemin:</strong> <?= e((string) ($databaseInfo['path'] ?? '—')) ?></div>
                  </div>
                  <?php if (!empty($databaseInfo['error'])): ?><div class="settings-desc" style="color:var(--danger);margin-top:8px"><?= e((string) $databaseInfo['error']) ?></div><?php endif; ?>
                  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:14px"><a class="btn btn-ghost-dark btn-sm" href="<?= BASE_URL ?>/admin/database/export"><i class="fas fa-download"></i> Exporter la base</a><input id="databaseImportFile" type="file" accept=".sqlite,.db" hidden><button type="button" class="btn btn-ghost-dark btn-sm" onclick="document.getElementById('databaseImportFile').click()"><i class="fas fa-upload"></i> Importer une base</button><span id="databaseImportStatus" class="settings-desc"></span></div>
                </div>
              <?php endif; ?>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px"><span id="settingsStatus" class="settings-desc" style="margin-right:auto"></span><button class="btn btn-secondary" type="submit"><i class="fas fa-save"></i> Enregistrer</button></div>
          </div>
        </div>
      </form>
    </div><!-- /parametres -->

  </main>
</div><!-- /page-dashboard -->


<!-- ══════════════════════════════════════
     PAGE 4: CANDIDATE FORM
══════════════════════════════════════ -->
<div id="page-candidate" class="page">
  <div class="cand-nav">
    <div style="width:36px;height:36px;background:var(--secondary);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px"><i class="fas fa-chart-line"></i></div>
    <span style="font-family:'Poppins',sans-serif;font-weight:700;font-size:18px;color:#fff">Criteval<em style="color:var(--secondary);font-style:normal">_pro</em></span>
    <div style="flex:1;margin:0 24px">
      <div class="progress-info" style="margin-bottom:4px">
        <span style="color:rgba(255,255,255,0.6);font-size:12px">Progression du formulaire</span>
        <span style="color:#fff;font-size:12px;font-weight:600">65%</span>
      </div>
      <div class="cand-progress-bar"><div class="cand-progress-fill" style="width:65%"></div></div>
    </div>
    <div class="autosave"><i class="fas fa-check-circle"></i> Sauvegardé à 14:32</div>
    <button class="btn btn-ghost btn-sm" style="margin-left:16px;border-color:rgba(255,255,255,0.2);color:rgba(255,255,255,0.7)" onclick="showPage('landing')"><i class="fas fa-arrow-left"></i> Retour</button>
  </div>
  <div class="cand-form-wrap">
    <div class="cand-form-header">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
        <span class="badge badge-success"><i class="fas fa-circle" style="font-size:8px"></i> Formulaire ouvert</span>
        <span style="color:var(--text-muted);font-size:13px">Clôture : 15 décembre 2025</span>
      </div>
      <h1 class="cand-form-title">Dossier de candidature — Programme AGRI-2025</h1>
      <p class="cand-form-desc">Ce formulaire vous permet de soumettre votre dossier de candidature au Programme d'Agriculture Durable 2025, financé par la FAO. Remplissez toutes les sections et joignez les documents requis.</p>
      <div style="display:flex;gap:16px;margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:6px;color:var(--text-muted);font-size:13px"><i class="fas fa-clock"></i> Environ 20 minutes</div>
        <div style="display:flex;align-items:center;gap:6px;color:var(--text-muted);font-size:13px"><i class="fas fa-globe-africa"></i> Afrique subsaharienne</div>
        <div style="display:flex;align-items:center;gap:6px;color:var(--text-muted);font-size:13px"><i class="fas fa-file-pdf"></i> PDF requis</div>
      </div>
    </div>

    <div class="cand-section">
      <div class="cand-section-title"><span style="width:28px;height:28px;background:var(--primary);color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">1</span> Informations du porteur de projet</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="cand-field">
          <label class="cand-label">Nom complet <span class="req">*</span></label>
          <div style="position:relative"><i class="fas fa-user input-icon-light" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted)"></i><input type="text" class="form-input-light" style="padding-left:40px" placeholder="Ex: Jean-Paul Kamga" value="Amara Konaté"></div>
        </div>
        <div class="cand-field">
          <label class="cand-label">Organisation <span class="req">*</span></label>
          <input type="text" class="form-input-light" placeholder="Nom de votre organisation" value="Coop. Agricole du Poro">
        </div>
        <div class="cand-field">
          <label class="cand-label">Pays <span class="req">*</span></label>
          <select class="form-input-light"><option>🇨🇮 Côte d'Ivoire</option><option>🇨🇲 Cameroun</option><option>🇸🇳 Sénégal</option><option>🇲🇱 Mali</option></select>
        </div>
        <div class="cand-field">
          <label class="cand-label">Téléphone</label>
          <input type="tel" class="form-input-light" placeholder="+225 01 23 45 67 89" value="+225 07 45 23 89 01">
        </div>
      </div>
    </div>

    <div class="cand-section">
      <div class="cand-section-title"><span style="width:28px;height:28px;background:var(--primary);color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">2</span> Description du projet</div>
      <div class="cand-field">
        <label class="cand-label">Titre du projet <span class="req">*</span></label>
        <input type="text" class="form-input-light" value="Système d'irrigation solaire pour les petits agriculteurs">
      </div>
      <div class="cand-field">
        <label class="cand-label">Description détaillée <span class="req">*</span></label>
        <textarea class="form-input-light" style="height:120px;resize:vertical">Notre projet vise à développer un système d'irrigation solaire pour les petits agriculteurs de la région de Korhogo. Nous prévoyons d'équiper 200 exploitations sur 24 mois, avec un impact estimé de 1500 familles bénéficiaires.</textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="cand-field">
          <label class="cand-label">Budget demandé (FCFA) <span class="req">*</span></label>
          <input type="number" class="form-input-light" value="45000000">
        </div>
        <div class="cand-field">
          <label class="cand-label">Durée du projet</label>
          <select class="form-input-light"><option>24 mois</option><option>12 mois</option><option>36 mois</option></select>
        </div>
      </div>
    </div>

    <div class="cand-section">
      <div class="cand-section-title"><span style="width:28px;height:28px;background:var(--primary);color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">3</span> Documents requis</div>
      <div class="cand-field">
        <label class="cand-label">Présentation du projet (PDF) <span class="req">*</span></label>
        <div style="border:2px dashed var(--border);border-radius:10px;padding:24px;text-align:center;cursor:pointer;transition:all 0.2s" onmouseover="this.style.borderColor='var(--secondary)'" onmouseout="this.style.borderColor='var(--border)'">
          <i class="fas fa-cloud-upload-alt" style="font-size:28px;color:var(--text-muted);display:block;margin-bottom:8px"></i>
          <p style="color:var(--text-muted);font-size:14px">Glissez votre PDF ici ou <span style="color:var(--secondary);font-weight:600;cursor:pointer">cliquez pour sélectionner</span></p>
          <p style="color:var(--text-muted);font-size:12px;margin-top:4px">PDF uniquement — Max 10 Mo</p>
          <div style="margin-top:12px;background:var(--secondary-light);border-radius:8px;padding:8px 12px;display:inline-flex;align-items:center;gap:8px;color:var(--secondary);font-size:13px"><i class="fas fa-file-pdf"></i> presentation_projet.pdf <i class="fas fa-check-circle"></i></div>
        </div>
      </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 0">
      <button class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Section précédente</button>
      <button class="btn btn-secondary btn-lg" onclick="showPage('landing');showToast('Candidature soumise avec succès ! Réf: AGRI-047', 'success')">
        <i class="fas fa-paper-plane"></i> Soumettre ma candidature
      </button>
    </div>
  </div>
</div><!-- /page-candidate -->


<!-- ══════════════════════════════════════
     MODALS
══════════════════════════════════════ -->
<div class="modal-overlay organisation-edit-overlay" id="modalNewProject" onclick="if(event.target===this)$(this).removeClass('open')">
  <div class="modal organisation-edit-modal">
    <div class="modal-header">
      <div class="organisation-edit-heading"><div class="organisation-edit-icon"><i class="fas fa-building"></i></div><div><div class="organisation-edit-eyebrow">Référentiel</div><div class="modal-title" id="organisationModalTitle">Nouvelle organisation</div><div class="organisation-edit-subtitle">Créez une fiche claire et complète pour le suivi des évaluations.</div></div></div>
      <button class="modal-close" onclick="$('#modalNewProject').removeClass('open')"><i class="fas fa-times"></i></button>
    </div>
    <form method="post" action="<?= BASE_URL ?>/admin/projects" enctype="multipart/form-data">
      <div class="modal-body organisation-modal-body">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="0">
        <input type="hidden" name="criteria_configured" value="1">
        <input type="hidden" name="criteria_selection_submitted" value="1">
        <div class="organisation-form-groups">
          <section class="organisation-form-group">
            <div class="organisation-form-group-heading"><i class="fas fa-id-card"></i><span>Identité</span></div>
            <div class="organisation-modal-grid organisation-modal-grid-identity">
              <label>Nom de l’organisation *<input required name="organization" class="form-input-dark" placeholder="Nom de votre organisation"></label>
              <label>Nom de la fiche *<input required name="title" class="form-input-dark" placeholder="Organisation CS4ME"></label>
              <label>Statut juridique<select name="legal_status" class="form-input-dark"><option value="non_legal">Non légale</option><option value="legal">Légale</option></select></label>
              <label>Pays *<input required name="country_code" class="form-input-dark" maxlength="5" placeholder="ML"></label>
            </div>
          </section>
          <section class="organisation-form-group">
            <div class="organisation-form-group-heading"><i class="fas fa-address-book"></i><span>Contact</span></div>
            <div class="organisation-modal-grid organisation-modal-grid-contact">
              <label>Responsable<input name="contact_name" class="form-input-dark"></label>
              <label>Téléphone<input type="tel" name="contact_phone" class="form-input-dark"></label>
              <label>E-mail<input type="email" name="contact_email" class="form-input-dark"></label>
              <label>Site web<input type="url" name="website" class="form-input-dark" placeholder="https://..."></label>
            </div>
          </section>
          <section class="organisation-form-group">
            <div class="organisation-form-group-heading"><i class="fas fa-bullseye"></i><span>Périmètre d’intervention</span></div>
            <div class="organisation-modal-grid organisation-modal-grid-reach">
              <label>Domaines thématiques<input name="domains" class="form-input-dark" placeholder="Femmes, jeunesse, santé"></label>
              <label>Publics cibles<input name="target_audiences" class="form-input-dark" placeholder="Femmes, hommes, enfants"></label>
              <label class="field-span-2">Zone d’intervention<input name="intervention_zone" class="form-input-dark"></label>
            </div>
          </section>
          <section class="organisation-form-group">
            <div class="organisation-form-group-heading"><i class="fas fa-chart-line"></i><span>Suivi</span></div>
            <div class="organisation-modal-grid organisation-modal-grid-tracking">
              <label>Projets<input type="number" min="0" name="project_count" class="form-input-dark"></label>
              <label>Durée (mois)<input type="number" min="1" name="duration_months" class="form-input-dark"></label>
              <label>Budget demandé (FCFA)<input type="number" min="0" step="0.01" name="budget_requested" class="form-input-dark"></label>
              <label>Statut<select name="status" class="form-input-dark"><option value="draft">Brouillon</option><option value="active">Active</option><option value="closed">Clôturée</option><option value="archived">Archivée</option></select></label>
              <label class="logo-field field-span-4">Logo<input type="file" name="logo" class="form-input-dark" accept="image/jpeg,image/png,image/webp,image/gif"><small>JPG, PNG, WEBP ou GIF, 2 Mo max.</small></label>
            </div>
          </section>
          <section class="organisation-form-group organisation-form-group-wide">
            <div class="organisation-form-group-heading"><i class="fas fa-list-check"></i><span>Critères d’évaluation de cette organisation</span><span class="organisation-criteria-hint">Les 10 critères standards sont proposés par défaut</span></div>
            <div class="organisation-template-grid" id="organisationTemplateCriteria">
              <?php foreach (($criteriaTemplates ?? []) as $template): ?>
                <label class="organisation-template-option"><input type="checkbox" name="criteria_template_ids[]" value="<?= (int) $template['id'] ?>"><span><?= e($template['label']) ?></span></label>
              <?php endforeach; ?>
            </div>
            <div class="organisation-custom-heading"><span>Critères personnalisés</span><button type="button" class="btn btn-ghost-dark btn-sm" id="addOrganisationCustomCriterion"><i class="fas fa-plus"></i> Ajouter</button></div>
            <div class="organisation-custom-list" id="organisationCustomCriteria"></div>
          </section>
        </div>
        <label class="organisation-description-field">Présentation / description<textarea name="description" class="form-input-dark" rows="2" placeholder="Activités, réalisations et résultats de votre organisation."></textarea></label>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost-dark" onclick="$('#modalNewProject').removeClass('open')">Annuler</button>
        <button class="btn btn-secondary" id="organisationModalSubmit" type="submit"><i class="fas fa-check"></i> Enregistrer l’organisation</button>
      </div>
    </form>
  </div>
</div>
<div class="modal-overlay organisation-preview-overlay" id="modalOrganisationPreview" role="dialog" aria-modal="true" aria-labelledby="organisationPreviewTitle" aria-hidden="true" onclick="if(event.target===this)closeOrganisationPreview()">
  <div class="organisation-preview-modal">
    <div class="organisation-preview-header">
      <div class="organisation-preview-heading">
        <div class="organisation-preview-logo" id="organisationPreviewLogo"><i class="fas fa-building"></i></div>
        <div>
          <div class="organisation-preview-kicker">Fiche organisation</div>
          <h2 id="organisationPreviewTitle">Aperçu de l’organisation</h2>
          <p id="organisationPreviewSubtitle"></p>
        </div>
      </div>
      <button class="modal-close" type="button" aria-label="Fermer l’aperçu" onclick="closeOrganisationPreview()"><i class="fas fa-times"></i></button>
    </div>
    <div class="organisation-preview-body">
      <div class="organisation-preview-status" id="organisationPreviewStatus"></div>
      <div class="organisation-preview-grid">
        <section class="organisation-preview-section organisation-preview-section-wide">
          <div class="organisation-preview-section-title"><i class="fas fa-chart-pie"></i><span>En bref</span></div>
          <div class="organisation-preview-facts" id="organisationPreviewFacts"></div>
        </section>
        <section class="organisation-preview-section">
          <div class="organisation-preview-section-title"><i class="fas fa-address-card"></i><span>Contact</span></div>
          <div class="organisation-preview-list" id="organisationPreviewContact"></div>
        </section>
        <section class="organisation-preview-section">
          <div class="organisation-preview-section-title"><i class="fas fa-bullseye"></i><span>Rayonnement</span></div>
          <div class="organisation-preview-list" id="organisationPreviewReach"></div>
        </section>
        <section class="organisation-preview-section organisation-preview-section-wide">
          <div class="organisation-preview-section-title"><i class="fas fa-align-left"></i><span>Présentation</span></div>
          <p class="organisation-preview-description" id="organisationPreviewDescription"></p>
        </section>
      </div>
    </div>
    <div class="organisation-preview-footer">
      <span class="organisation-preview-created" id="organisationPreviewCreated"></span>
      <div class="organisation-preview-actions">
        <button type="button" class="btn btn-ghost-dark" onclick="closeOrganisationPreview()">Fermer</button>
        <button type="button" class="btn btn-secondary" id="organisationPreviewEdit"><i class="fas fa-edit"></i> Modifier</button>
      </div>
    </div>
  </div>
</div>
<style>
.organisation-preview-overlay{align-items:center;backdrop-filter:blur(7px);background:rgba(5,12,18,.78);display:none;justify-content:center;padding:5vh 5vw;z-index:1200}.organisation-preview-overlay.open{display:flex}
.organisation-preview-modal{background:#18232b;border:1px solid rgba(255,255,255,.12);border-radius:18px;box-shadow:0 28px 90px rgba(0,0,0,.45);color:#fff;display:flex;flex-direction:column;height:80vh;max-height:80vh;max-width:1100px;overflow:hidden;width:80vw}
.organisation-preview-header{align-items:flex-start;border-bottom:1px solid rgba(255,255,255,.09);display:flex;gap:24px;justify-content:space-between;padding:30px 34px 24px}
.organisation-preview-heading{align-items:center;display:flex;gap:18px;min-width:0}
.organisation-preview-logo{align-items:center;background:rgba(46,175,125,.12);border:1px solid rgba(46,175,125,.26);border-radius:14px;color:var(--secondary);display:flex;flex:0 0 78px;font-size:28px;height:78px;justify-content:center;overflow:hidden;width:78px}
.organisation-preview-logo img{height:100%;object-fit:contain;padding:10px;width:100%}
.organisation-preview-kicker{color:var(--secondary);font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.organisation-preview-header h2{font-size:26px;line-height:1.2;margin:5px 0 4px;overflow-wrap:anywhere}
.organisation-preview-header p{color:rgba(255,255,255,.5);font-size:13px;margin:0}
.organisation-preview-body{overflow:auto;padding:26px 34px}
.organisation-preview-status{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:22px}
.organisation-preview-status .badge{font-size:11px}
.organisation-preview-grid{display:grid;gap:18px;grid-template-columns:repeat(2,minmax(0,1fr))}
.organisation-preview-section{background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:20px}
.organisation-preview-section-wide{grid-column:1/-1}
.organisation-preview-section-title{align-items:center;color:rgba(255,255,255,.78);display:flex;font-size:13px;font-weight:700;gap:9px;margin-bottom:16px;text-transform:uppercase;letter-spacing:.06em}
.organisation-preview-section-title i{color:var(--secondary);font-size:14px}
.organisation-preview-facts{display:grid;gap:18px;grid-template-columns:repeat(4,minmax(0,1fr))}
.organisation-preview-fact-label,.organisation-preview-item-label{color:rgba(255,255,255,.42);font-size:11px;margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em}
.organisation-preview-fact-value,.organisation-preview-item-value{color:#fff;font-size:14px;overflow-wrap:anywhere}
.organisation-preview-list{display:grid;gap:13px}
.organisation-preview-item-value a{color:#8de0bd;text-decoration:none}
.organisation-preview-item-value a:hover{text-decoration:underline}
.organisation-preview-description{color:rgba(255,255,255,.7);font-size:14px;line-height:1.75;margin:0;white-space:pre-line;overflow-wrap:anywhere}
.organisation-preview-footer{align-items:center;border-top:1px solid rgba(255,255,255,.09);display:flex;gap:18px;justify-content:space-between;padding:18px 34px}
.organisation-preview-created{color:rgba(255,255,255,.38);font-size:12px}
.organisation-preview-actions{display:flex;gap:10px}
@media(max-width:760px){.organisation-preview-overlay{padding:0}.organisation-preview-modal{border-radius:0;height:100vh;max-height:100vh;width:100vw}.organisation-preview-header,.organisation-preview-body{padding-left:20px;padding-right:20px}.organisation-preview-header h2{font-size:21px}.organisation-preview-logo{flex-basis:60px;height:60px;width:60px}.organisation-preview-grid{grid-template-columns:1fr}.organisation-preview-section-wide{grid-column:auto}.organisation-preview-facts{grid-template-columns:repeat(2,minmax(0,1fr))}.organisation-preview-footer{align-items:stretch;flex-direction:column;padding:16px 20px}.organisation-preview-actions{justify-content:flex-end}}
</style>
<style>
.organisation-edit-overlay{padding:24px}
.organisation-edit-modal{width:min(920px,96vw);max-width:920px;max-height:min(900px,94vh);display:flex;flex-direction:column;overflow:hidden;background:#202031;border-color:rgba(255,255,255,.13);box-shadow:0 24px 80px rgba(0,0,0,.45)}
.organisation-edit-modal .modal-header{align-items:flex-start;border-bottom:1px solid rgba(255,255,255,.08);padding:24px 28px 20px}
.organisation-edit-heading{align-items:center;display:flex;gap:14px;min-width:0}
.organisation-edit-icon{align-items:center;background:rgba(46,175,125,.13);border:1px solid rgba(46,175,125,.25);border-radius:12px;color:var(--secondary);display:flex;flex:0 0 42px;font-size:17px;height:42px;justify-content:center;width:42px}
.organisation-edit-eyebrow{color:var(--secondary);font-size:10px;font-weight:700;letter-spacing:.14em;margin-bottom:3px;text-transform:uppercase}
.organisation-edit-modal .modal-title{font-size:20px;line-height:1.2}
.organisation-edit-subtitle{color:rgba(255,255,255,.42);font-size:12px;margin-top:5px}
.organisation-edit-modal form{display:flex;flex:1 1 auto;flex-direction:column;min-height:0}
.organisation-edit-modal .modal-body{flex:1 1 auto;max-height:none;min-height:0;overflow-y:auto;padding:18px 28px 14px}
.organisation-form-groups{display:grid;gap:10px;grid-template-columns:1fr 1fr}
.organisation-form-group{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:12px 14px}
.organisation-form-group-wide{grid-column:1/-1}
.organisation-form-group-heading{align-items:center;color:rgba(255,255,255,.72);display:flex;font-size:10px;font-weight:700;gap:7px;letter-spacing:.1em;margin-bottom:10px;text-transform:uppercase}
.organisation-form-group-heading i{color:var(--secondary);font-size:12px}
.organisation-criteria-hint{color:rgba(255,255,255,.35);font-size:10px;font-weight:400;letter-spacing:0;margin-left:auto;text-transform:none}
.organisation-template-grid{display:grid;gap:6px 14px;grid-template-columns:repeat(2,minmax(0,1fr));max-height:118px;overflow:auto}
.organisation-template-option{align-items:flex-start!important;color:rgba(255,255,255,.68)!important;cursor:pointer;display:flex!important;flex-direction:row!important;font-size:11px!important;font-weight:500!important;gap:8px!important;line-height:1.25;min-width:0}
.organisation-template-option input{accent-color:var(--secondary);flex:0 0 auto;margin:1px 0 0}
.organisation-template-option span{overflow-wrap:anywhere}
.organisation-custom-heading{align-items:center;border-top:1px solid rgba(255,255,255,.07);display:flex;justify-content:space-between;margin-top:11px;padding-top:9px}
.organisation-custom-heading span{color:rgba(255,255,255,.55);font-size:11px;font-weight:600}
.organisation-custom-list{display:grid;gap:7px;margin-top:8px}
.organisation-custom-row{align-items:center;display:grid;gap:7px;grid-template-columns:1.8fr 1.8fr .55fr .65fr 24px}
.organisation-custom-row .form-input-dark{font-size:11px!important;min-height:32px!important;padding:6px 8px!important}
.organisation-custom-row .custom-required{accent-color:var(--secondary);justify-self:center}
.organisation-custom-remove{align-items:center;background:rgba(231,76,60,.12);border:0;border-radius:6px;color:var(--danger);cursor:pointer;display:flex;height:28px;justify-content:center;width:24px}
.organisation-custom-empty{color:rgba(255,255,255,.3);font-size:10px}
.organisation-edit-modal .organisation-modal-grid{display:grid!important;gap:9px 12px!important;grid-template-columns:repeat(2,minmax(0,1fr))!important}
.organisation-edit-modal .organisation-modal-grid-identity{grid-template-columns:repeat(2,minmax(0,1fr))!important}
.organisation-edit-modal .organisation-modal-grid-tracking{grid-template-columns:.8fr .9fr 1.35fr 1fr!important}
.organisation-edit-modal .field-span-2{grid-column:1/-1}
.organisation-edit-modal .field-span-4{grid-column:1/-1}
.organisation-edit-modal .organisation-modal-body label{align-items:stretch;color:rgba(255,255,255,.64);display:flex;flex-direction:column;font-size:12px;font-weight:600;gap:7px;margin:0;min-width:0}
.organisation-edit-modal .organisation-modal-body label > .form-input-dark{box-sizing:border-box;width:100%}
.organisation-edit-modal .form-input-dark{background:#2b2b3d;border:1px solid rgba(255,255,255,.1);border-radius:9px;color:#f5f7fa;font-size:13px;min-height:40px;padding:10px 12px;transition:border-color .2s,box-shadow .2s,background .2s}
.organisation-edit-modal .form-input-dark:hover{background:#303044}
.organisation-edit-modal .form-input-dark:focus{background:#303044;border-color:var(--secondary);box-shadow:0 0 0 3px rgba(46,175,125,.12);outline:none}
.organisation-edit-modal textarea.form-input-dark{line-height:1.4;margin-top:6px;min-height:58px;resize:vertical}
.organisation-edit-modal input[type=file].form-input-dark{color:rgba(255,255,255,.62);font-size:12px;padding:7px}
.organisation-edit-modal input[type=file]::file-selector-button{background:rgba(46,175,125,.16);border:1px solid rgba(46,175,125,.25);border-radius:6px;color:#a8e8ce;cursor:pointer;margin-right:10px;padding:6px 9px}
.organisation-edit-modal .organisation-modal-body small{color:rgba(255,255,255,.38);font-size:10px;font-weight:400;line-height:1.2}
.organisation-description-field{color:rgba(255,255,255,.64);display:flex;flex-direction:column;font-size:12px;font-weight:600;gap:0;margin-top:12px}
.organisation-edit-modal .modal-footer{align-items:center;background:rgba(0,0,0,.12);border-top:1px solid rgba(255,255,255,.08);flex:0 0 auto;padding:16px 28px 18px}
.organisation-edit-modal .modal-footer .btn{min-height:40px}
@media(max-width:700px){.organisation-edit-overlay{padding:0}.organisation-edit-modal{border-radius:0;height:100vh;max-height:100vh;width:100vw}.organisation-edit-modal .modal-header,.organisation-edit-modal .modal-body{padding-left:20px;padding-right:20px}.organisation-form-groups{grid-template-columns:1fr}.organisation-form-group-wide{grid-column:auto}.organisation-criteria-hint{display:none}.organisation-template-grid{grid-template-columns:1fr;max-height:150px}.organisation-custom-row{grid-template-columns:1fr 1fr 52px 58px 24px}.organisation-custom-row .custom-description{grid-column:1/-1}.organisation-edit-modal .organisation-modal-grid,.organisation-edit-modal .organisation-modal-grid-identity,.organisation-edit-modal .organisation-modal-grid-tracking{grid-template-columns:1fr 1fr!important}.organisation-edit-modal .modal-footer{padding-left:20px;padding-right:20px}.organisation-edit-subtitle{max-width:230px}}
</style>
<script>
let organisationPreviewLastFocus = null;

function selectTrainingSession(sessionId) {
  if (!sessionId) return;
  const url = new URL(window.location.href);
  url.searchParams.set('training_id', sessionId);
  window.location.href = url.toString();
}

function exportTrainingRegister() {
  const rows = Array.from(document.querySelectorAll('#module-formation tbody tr[data-submission-id]'));
  const values = [['Participant', 'Presence', 'Note', 'Commentaire', 'Inclus dans evaluation']];
  rows.forEach(function (row) {
    values.push([
      row.cells[0]?.textContent.trim() || '',
      row.querySelector('.training-attendance')?.value || '',
      row.querySelector('.training-grade')?.value || '',
      row.querySelector('.training-comment')?.value || '',
      row.querySelector('.training-included')?.checked ? 'Oui' : 'Non'
    ]);
  });
  const csv = values.map(function (line) { return line.map(function (value) { return '"' + String(value).replaceAll('"', '""') + '"'; }).join(';'); }).join('\n');
  const link = document.createElement('a');
  link.href = URL.createObjectURL(new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' }));
  link.download = 'registre-formation.csv';
  link.click();
  URL.revokeObjectURL(link.href);
}

async function saveTrainingParticipant(row) {
  const data = new FormData();
  data.append('csrf_token', document.querySelector('#modalNewProject input[name="csrf_token"]')?.value || '');
  data.append('session_id', '<?= (int) ($selectedTraining['id'] ?? 0) ?>');
  data.append('submission_id', row.dataset.submissionId);
  data.append('attendance_status', row.querySelector('.training-attendance').value);
  data.append('grade', row.querySelector('.training-grade').value);
  data.append('comment', row.querySelector('.training-comment').value);
  if (row.querySelector('.training-included').checked) data.append('included', '1');
  const response = await fetch('<?= BASE_URL ?>/admin/sessions/participant', { method: 'POST', body: data });
  const result = await response.json();
  if (!response.ok || !result.success) throw new Error(result.message || 'Impossible d’enregistrer le participant.');
}

async function saveAllTrainingParticipants() {
  const rows = Array.from(document.querySelectorAll('#module-formation tbody tr[data-submission-id]'));
  try {
    await Promise.all(rows.map(saveTrainingParticipant));
    showToast('Présences et notes enregistrées.', 'success');
    window.location.reload();
  } catch (error) {
    showToast(error.message, 'error');
  }
}

function organisationPreviewValue(value, fallback) {
  return value === null || value === undefined || String(value).trim() === '' ? (fallback || 'Non renseigné') : String(value);
}

function organisationPreviewItem(label, value, link) {
  const item = document.createElement('div');
  item.className = 'organisation-preview-item';
  const itemLabel = document.createElement('div');
  itemLabel.className = 'organisation-preview-item-label';
  itemLabel.textContent = label;
  const itemValue = document.createElement('div');
  itemValue.className = 'organisation-preview-item-value';
  if (link && value) {
    const anchor = document.createElement('a');
    anchor.href = link;
    anchor.textContent = value;
    if (link.indexOf('http') === 0) {
      anchor.target = '_blank';
      anchor.rel = 'noopener noreferrer';
    }
    itemValue.appendChild(anchor);
  } else {
    itemValue.textContent = organisationPreviewValue(value);
  }
  item.append(itemLabel, itemValue);
  return item;
}

function openOrganisationPreview(project) {
  const modal = document.getElementById('modalOrganisationPreview');
  const title = organisationPreviewValue(project.organization || project.title, 'Organisation');
  const website = String(project.website || '').trim();
  const websiteUrl = /^https?:\/\//i.test(website) ? website : (website && !/^[a-z][a-z0-9+.-]*:/i.test(website) ? 'https://' + website : '');
  const budget = project.budget_requested === null || project.budget_requested === undefined || project.budget_requested === ''
    ? 'Non renseigné'
    : new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(project.budget_requested)) + ' FCFA';
  const statusLabels = { draft: 'Brouillon', active: 'Active', closed: 'Clôturée', archived: 'Archivée' };
  const statusClass = project.status === 'active' ? 'badge-success' : (project.status === 'closed' ? 'badge-warning' : 'badge-muted');
  const logo = document.getElementById('organisationPreviewLogo');
  const facts = document.getElementById('organisationPreviewFacts');
  const contact = document.getElementById('organisationPreviewContact');
  const reach = document.getElementById('organisationPreviewReach');
  organisationPreviewLastFocus = document.activeElement;

  document.getElementById('organisationPreviewTitle').textContent = title;
  document.getElementById('organisationPreviewSubtitle').textContent = organisationPreviewValue(project.title, 'Fiche organisation');
  document.getElementById('organisationPreviewDescription').textContent = organisationPreviewValue(project.description, 'Aucune présentation renseignée.');
  document.getElementById('organisationPreviewCreated').textContent = project.created_at ? 'Créée le ' + new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' }).format(new Date(project.created_at.replace(' ', 'T'))) : '';

  logo.replaceChildren();
  if (project.logo_path) {
    const image = document.createElement('img');
    image.src = '<?= BASE_URL ?>/' + String(project.logo_path).replace(/^\/+/, '');
    image.alt = 'Logo ' + title;
    image.onerror = function () { logo.replaceChildren(); logo.innerHTML = '<i class="fas fa-building"></i>'; };
    logo.appendChild(image);
  } else {
    logo.innerHTML = '<i class="fas fa-building"></i>';
  }

  const status = document.getElementById('organisationPreviewStatus');
  status.replaceChildren();
  const statusBadge = document.createElement('span');
  statusBadge.className = 'badge ' + statusClass;
  statusBadge.textContent = statusLabels[project.status] || organisationPreviewValue(project.status, 'Brouillon');
  const legalBadge = document.createElement('span');
  legalBadge.className = 'badge badge-muted';
  legalBadge.textContent = project.legal_status === 'legal' ? 'Organisation légale' : 'Organisation non légale';
  status.append(statusBadge, legalBadge);

  facts.replaceChildren(
    organisationPreviewItem('Pays', project.country_code),
    organisationPreviewItem('Zone d’intervention', project.intervention_zone),
    organisationPreviewItem('Projets', project.project_count === null || project.project_count === undefined || project.project_count === '' ? null : project.project_count),
    organisationPreviewItem('Budget demandé', budget),
    organisationPreviewItem('Durée', project.duration_months ? project.duration_months + ' mois' : null)
  );
  contact.replaceChildren(
    organisationPreviewItem('Responsable', project.contact_name),
    organisationPreviewItem('Téléphone', project.contact_phone, project.contact_phone ? 'tel:' + project.contact_phone : ''),
    organisationPreviewItem('E-mail', project.contact_email, project.contact_email ? 'mailto:' + project.contact_email : ''),
    organisationPreviewItem('Site web', website, websiteUrl)
  );
  reach.replaceChildren(
    organisationPreviewItem('Domaines thématiques', project.domains),
    organisationPreviewItem('Publics cibles', project.target_audiences)
  );

  document.getElementById('organisationPreviewEdit').onclick = function () {
    closeOrganisationPreview();
    openOrganisationModal(project);
  };
  modal.classList.add('open');
  modal.setAttribute('aria-hidden', 'false');
  modal.querySelector('.modal-close').focus();
}

function closeOrganisationPreview() {
  const modal = document.getElementById('modalOrganisationPreview');
  if (!modal) return;
  modal.classList.remove('open');
  modal.setAttribute('aria-hidden', 'true');
  if (organisationPreviewLastFocus && typeof organisationPreviewLastFocus.focus === 'function') organisationPreviewLastFocus.focus();
}

document.addEventListener('keydown', function (event) {
  if (event.key === 'Escape' && document.getElementById('modalOrganisationPreview')?.classList.contains('open')) closeOrganisationPreview();
});

function openOrganisationModal(project) {
  const form = document.querySelector('#modalNewProject form');
  const fields = ['organization', 'title', 'contact_name', 'contact_phone', 'contact_email', 'website', 'domains', 'target_audiences', 'country_code', 'project_count', 'intervention_zone', 'budget_requested', 'duration_months', 'description', 'status', 'legal_status'];
  fields.forEach(function (name) {
    const field = form.querySelector('[name="' + name + '"]');
    if (field) field.value = project[name] ?? '';
  });
  populateOrganisationCriteria(form, project);
  form.querySelector('[name="id"]').value = project.id || 0;
  document.getElementById('organisationModalTitle').innerHTML = '<i class="fas fa-edit" style="color:var(--secondary);margin-right:8px"></i> Modifier l’organisation';
  document.getElementById('organisationModalSubmit').innerHTML = '<i class="fas fa-save"></i> Enregistrer les modifications';
  $('#modalNewProject').addClass('open');
}

function openNewOrganisationModal() {
  const form = document.querySelector('#modalNewProject form');
  openOrganisationModal({ id: 0, criteria_configuration: [] });
  form.querySelector('[name="id"]').value = '0';
  document.getElementById('organisationModalTitle').textContent = 'Nouvelle organisation';
  document.getElementById('organisationModalSubmit').innerHTML = '<i class="fas fa-check"></i> Enregistrer l’organisation';
}

function createOrganisationCustomRow(criteria) {
  const list = document.getElementById('organisationCustomCriteria');
  const index = Number(list.dataset.nextIndex || 0);
  list.dataset.nextIndex = String(index + 1);
  const row = document.createElement('div');
  row.className = 'organisation-custom-row';
  const values = criteria || {};
  const fields = [
    ['label', 'Libellé du critère', values.label || ''],
    ['description', 'Description courte', values.description || ''],
    ['weight', 'Poids', values.weight ?? 1],
    ['max_score', 'Note max.', values.max_score ?? 20]
  ];
  if (values.id) {
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'custom_criteria[' + index + '][id]';
    hidden.value = values.id;
    row.appendChild(hidden);
  }
  fields.forEach(function (field) {
    const input = document.createElement('input');
    input.className = 'form-input-dark custom-' + field[0];
    input.name = 'custom_criteria[' + index + '][' + field[0] + ']';
    input.placeholder = field[1];
    input.value = field[2];
    if (field[0] === 'weight' || field[0] === 'max_score') {
      input.type = 'number';
      input.min = '0.01';
      input.step = '0.01';
    } else {
      input.type = 'text';
    }
    row.appendChild(input);
  });
  const required = document.createElement('input');
  required.type = 'checkbox';
  required.className = 'custom-required';
  required.name = 'custom_criteria[' + index + '][is_required]';
  required.checked = values.is_required === undefined ? true : Boolean(Number(values.is_required));
  required.title = 'Critère obligatoire';
  row.appendChild(required);
  const remove = document.createElement('button');
  remove.type = 'button';
  remove.className = 'organisation-custom-remove';
  remove.title = 'Retirer ce critère';
  remove.innerHTML = '<i class="fas fa-times"></i>';
  remove.addEventListener('click', function () { row.remove(); });
  row.appendChild(remove);
  list.appendChild(row);
}

function populateOrganisationCriteria(form, project) {
  const checkboxes = form.querySelectorAll('input[name="criteria_template_ids[]"]');
  const configuration = Array.isArray(project.criteria_configuration) ? project.criteria_configuration : [];
  const selectedTemplateIds = new Set(configuration.filter(function (item) { return item.source_template_id; }).map(function (item) { return String(item.source_template_id); }));
  checkboxes.forEach(function (checkbox) {
    checkbox.checked = !project.id || selectedTemplateIds.has(String(checkbox.value));
  });
  const customList = document.getElementById('organisationCustomCriteria');
  customList.replaceChildren();
  customList.dataset.nextIndex = '0';
  configuration.filter(function (item) { return !item.source_template_id; }).forEach(createOrganisationCustomRow);
}

document.getElementById('addOrganisationCustomCriterion')?.addEventListener('click', function () {
  createOrganisationCustomRow({});
});

function confirmOrganisationDelete(id, name) {
  document.getElementById('organisationDeleteId').value = id;
  document.getElementById('organisationDeleteName').textContent = name;
  $('#modalDelete').addClass('open');
}
</script>

<div class="modal-overlay" id="modalTraining" onclick="if(event.target===this)$(this).removeClass('open')">
  <div class="modal" style="max-width:720px">
    <div class="modal-header">
      <div class="modal-title"><i class="fas fa-chalkboard-teacher" style="color:var(--secondary);margin-right:8px"></i> Nouvelle formation</div>
      <button class="modal-close" onclick="$('#modalTraining').removeClass('open')"><i class="fas fa-times"></i></button>
    </div>
    <form method="post" action="<?= BASE_URL ?>/admin/sessions" id="trainingCreateForm">
    <div class="modal-body">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div style="display:grid;grid-template-columns:1.2fr 0.8fr;gap:16px;margin-bottom:16px">
        <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Titre de la formation *</label><input name="name" required class="form-input-dark" id="trainingTitle" style="width:100%" placeholder="Ex: Préparation au pitch financement"></div>
        <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Organisation liée *</label><select name="project_id" class="form-input-dark" style="width:100%" required><option value="">Choisir une organisation</option><?php foreach (($projects ?? []) as $project): ?><option value="<?= (int) $project['id'] ?>"><?= e($project['organization'] ?: $project['title']) ?></option><?php endforeach; ?></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">
        <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Date</label><input type="date" name="session_date" required class="form-input-dark" style="width:100%"></div>
        <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Date fin</label><input type="date" class="form-input-dark" style="width:100%"></div>
        <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Capacité</label><input type="number" name="capacity" class="form-input-dark" style="width:100%" value="50" min="1"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Format</label><select name="format" class="form-input-dark" style="width:100%"><option value="hybride">Hybride</option><option value="presentiel">Présentiel</option><option value="en_ligne">En ligne</option></select></div>
        <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Formateur / organisme</label><input class="form-input-dark" style="width:100%" placeholder="Ex: Cabinet Impact Afrique"></div>
      </div>
      <div style="margin-bottom:16px"><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Objectifs pédagogiques</label><textarea name="objective" required class="form-input-dark" style="width:100%;height:84px;resize:none" placeholder="Compétences attendues, livrables, critères de validation..."></textarea></div>
      <div style="background:rgba(46,175,125,0.08);border:1px solid rgba(46,175,125,0.2);border-radius:10px;padding:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px">
          <div><div style="color:#fff;font-weight:600">Rendre la note exploitable dans les évaluations</div><div style="color:rgba(255,255,255,0.45);font-size:12px;margin-top:3px">La formation apparaîtra comme un critère optionnel, traçable et désactivable.</div></div>
          <div class="toggle-switch on" onclick="$(this).toggleClass('on')"><div class="toggle-knob"></div></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px">
          <div><label class="form-label" style="color:rgba(255,255,255,0.55);font-size:12px;display:block;margin-bottom:6px">Poids maximum dans l'évaluation</label><input type="number" name="evaluation_weight" class="form-input-dark" style="width:100%" value="0" min="0" max="100"></div>
          <div><label class="form-label" style="color:rgba(255,255,255,0.55);font-size:12px;display:block;margin-bottom:6px">Note minimale de validation</label><input type="number" class="form-input-dark" style="width:100%" value="12"></div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost-dark" onclick="$('#modalTraining').removeClass('open')">Annuler</button>
      <button class="btn btn-ghost-dark" type="submit" name="is_active" value="0"><i class="fas fa-save"></i> Brouillon</button>
      <button class="btn btn-secondary" type="submit" name="is_active" value="1"><i class="fas fa-check"></i> Créer et activer</button>
    </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalDelete" onclick="if(event.target===this)$(this).removeClass('open')">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <div class="modal-title"><i class="fas fa-exclamation-triangle" style="color:var(--danger);margin-right:8px"></i> Confirmer la suppression</div>
      <button class="modal-close" onclick="$('#modalDelete').removeClass('open')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <p style="color:rgba(255,255,255,0.7);font-size:14px;line-height:1.6">Êtes-vous sûr de vouloir supprimer <strong id="organisationDeleteName" style="color:#fff"></strong> ? Cette action est <strong style="color:var(--danger)">irréversible</strong>.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost-dark" onclick="$('#modalDelete').removeClass('open')">Annuler</button>
      <form method="post" action="<?= BASE_URL ?>/admin/projects/delete" id="organisationDeleteForm">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" id="organisationDeleteId" value="">
        <button class="btn btn-danger" type="submit"><i class="fas fa-trash"></i> Supprimer définitivement</button>
      </form>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modalPlanning" onclick="if(event.target===this)$(this).removeClass('open')">
  <div class="modal" style="max-width:520px">
    <div class="modal-header">
      <div class="modal-title"><i class="fas fa-calendar-plus" style="color:var(--secondary);margin-right:8px"></i> Planifier un formulaire</div>
      <button class="modal-close" onclick="$('#modalPlanning').removeClass('open')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div style="display:grid;gap:14px">
        <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Formulaire *</label><select class="form-input-dark" style="width:100%"><option>Dossier AGRI-2025</option><option>Formulaire Innovation</option></select></div>
        <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Accès</label><div style="display:flex;gap:8px;flex-wrap:wrap"><label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:rgba(255,255,255,0.6);font-size:13px"><input type="radio" name="access" checked style="accent-color:var(--secondary)"> Lien public</label><label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:rgba(255,255,255,0.6);font-size:13px"><input type="radio" name="access" style="accent-color:var(--secondary)"> Emails présélectionnés</label><label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:rgba(255,255,255,0.6);font-size:13px"><input type="radio" name="access" style="accent-color:var(--secondary)"> Admins seulement</label></div></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Date d'ouverture *</label><input type="date" class="form-input-dark" style="width:100%" value="2025-12-01"></div>
          <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Date de clôture *</label><input type="date" class="form-input-dark" style="width:100%" value="2025-12-31"></div>
        </div>
        <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Pays éligibles</label><select class="form-input-dark" style="width:100%" multiple style="height:80px"><option selected>Côte d'Ivoire</option><option selected>Cameroun</option><option selected>Sénégal</option><option>Mali</option><option>Guinée</option></select></div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-top:1px solid var(--border-dark)"><span style="color:rgba(255,255,255,0.7)">Activer immédiatement</span><div class="toggle-switch on" onclick="$(this).toggleClass('on')"><div class="toggle-knob"></div></div></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost-dark" onclick="$('#modalPlanning').removeClass('open')">Annuler</button>
      <button class="btn btn-secondary" onclick="$('#modalPlanning').removeClass('open');showToast('Planification enregistrée !', 'success')"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modalDashboardUser" role="dialog" aria-modal="true" onclick="if(event.target===this)closeDashboardUserModal()">
  <div class="modal" style="max-width:760px">
    <div class="modal-header">
      <div class="modal-title" id="dashboardUserModalTitle"><i class="fas fa-user-plus" style="color:var(--secondary);margin-right:8px"></i> Nouvel utilisateur</div>
      <button class="modal-close" type="button" onclick="closeDashboardUserModal()"><i class="fas fa-times"></i></button>
    </div>
    <form id="dashboardUserForm">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Nom complet</label><input name="name" class="form-input-dark" style="width:100%" required></div>
          <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Nom utilisateur</label><input name="username" class="form-input-dark" style="width:100%" required></div>
          <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Email</label><input type="email" name="email" class="form-input-dark" style="width:100%" required></div>
          <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Rôle</label><select name="role" class="form-input-dark" style="width:100%"><?php foreach ($availableRoles as $role): ?><option value="<?= e($role) ?>"><?= e(role_label($role)) ?></option><?php endforeach; ?></select></div>
          <div><label class="form-label" style="color:rgba(255,255,255,0.6);font-size:13px;display:block;margin-bottom:6px">Mot de passe</label><input type="password" name="password" class="form-input-dark" style="width:100%" autocomplete="new-password"></div>
          <label style="display:flex;align-items:center;gap:8px;color:rgba(255,255,255,0.7);margin-top:26px"><input type="checkbox" name="is_active" value="1" checked style="accent-color:var(--secondary)"> Compte actif</label>
        </div>
        <div style="margin-top:16px">
          <div class="settings-section-title" style="font-size:13px"><i class="fas fa-key" style="color:var(--accent)"></i> Permissions</div>
          <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px">
            <?php foreach ($modulePermissionLabels as $moduleKey => $moduleLabel): ?>
              <div style="border:1px solid var(--border-dark);border-radius:8px;padding:10px">
                <div style="color:#fff;font-size:13px;font-weight:600;margin-bottom:8px"><?= e($moduleLabel) ?></div>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                  <?php foreach ($permissionActionLabels as $actionKey => $actionLabel): ?>
                    <label style="display:flex;align-items:center;gap:5px;color:rgba(255,255,255,0.6);font-size:12px"><input type="checkbox" name="permissions[modules][<?= e($moduleKey) ?>][<?= e($actionKey) ?>]" value="1" style="accent-color:var(--secondary)"> <?= e($actionLabel) ?></label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div id="dashboardUserStatus" class="settings-desc" style="margin-top:12px"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost-dark" type="button" onclick="closeDashboardUserModal()">Annuler</button>
        <button class="btn btn-secondary" type="submit"><i class="fas fa-save"></i> Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
// ══════════════════════════════════════
// ROLE LABELS
// ══════════════════════════════════════
const ROLE_LABELS = {
  'superadmin': 'Super administrateur',
  'admin': 'Administrateur',
  'user': 'Utilisateur',
  'visitor': 'Visiteur'
};

function getRoleLabel(role) {
  return ROLE_LABELS[role] || role.charAt(0).toUpperCase() + role.slice(1);
}

// ══════════════════════════════════════
// PAGE SYSTEM
// ══════════════════════════════════════
function showPage(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  const targetPage = document.getElementById('page-' + name);
  if (!targetPage) {
    window.location.href = name === 'landing' ? 'index.html' : name + '.html';
    return;
  }
  targetPage.classList.add('active');
  if (name === 'landing' && typeof initLanding === 'function') initLanding();
  if (name === 'dashboard') initDashboard();
}

function loginToDash() {
  showPage('dashboard');
  setTimeout(() => initCharts(), 300);
  animateKPIs();
}

// ══════════════════════════════════════
// MODULE SWITCHING
// ══════════════════════════════════════
const moduleLabels = {
  apercu: 'Aperçu',
  projets: 'Projets',
  criteres: 'Critères d\'évaluation',
  formulaires: 'Formulaires',
  formation: 'Formation',
  evaluations: 'Évaluations',
  classements: 'Classements',
  calendrier: 'Planning',
  parametres: 'Paramètres'
};
const DASHBOARD_MODULE_STORAGE_KEY = 'criteval.activeModule';
const sidebarModuleCounts = <?= json_encode($moduleCounts, JSON_UNESCAPED_SLASHES) ?>;
const dashboardRoleDefaults = <?= json_encode($rolePermissionDefaults, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
const overviewData = <?= json_encode($overview, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

function renderSidebarCounts() {
  document.querySelectorAll('.sidebar-item').forEach(item => {
    const onclick = item.getAttribute('onclick') || '';
    const match = onclick.match(/switchModule\('([^']+)'/);
    const module = item.dataset.module || (match ? match[1] : '');
    if (!module || !Object.prototype.hasOwnProperty.call(sidebarModuleCounts, module)) return;

    let badge = item.querySelector('.sidebar-badge');
    const count = Number(sidebarModuleCounts[module] || 0);
    if (count <= 0) {
      if (badge) badge.remove();
      return;
    }

    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'sidebar-badge';
      item.appendChild(document.createTextNode(' '));
      item.appendChild(badge);
    }
    badge.textContent = count;
  });
}

function switchSettingsPanel(name, trigger = null) {
  document.querySelectorAll('.settings-pane').forEach(panel => panel.classList.add('hidden'));
  const panel = document.getElementById('settings-' + name);
  if (panel) panel.classList.remove('hidden');
  document.querySelectorAll('.settings-nav-item').forEach(item => item.classList.remove('active'));
  if (trigger) trigger.classList.add('active');
}

function syncToggleInputs(root = document) {
  root.querySelectorAll('.toggle-switch input[type="checkbox"]').forEach(input => {
    const toggle = input.closest('.toggle-switch');
    if (!toggle || toggle.dataset.bound === '1') return;
    toggle.dataset.bound = '1';
    toggle.addEventListener('click', event => {
      if (event.target === input) return;
      input.checked = !input.checked;
      toggle.classList.toggle('on', input.checked);
    });
    input.addEventListener('change', () => toggle.classList.toggle('on', input.checked));
  });
}

function initSettingsPanel() {
  const form = document.getElementById('settingsForm');
  if (!form || form.dataset.bound === '1') return;
  form.dataset.bound = '1';
  syncToggleInputs(form);

  const status = document.getElementById('settingsStatus');
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (status) status.textContent = 'Enregistrement...';
    const disabled = Array.from(form.querySelectorAll(':disabled'));
    disabled.forEach(el => el.disabled = false);
    try {
      const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
      const result = await response.json().catch(() => ({}));
      if (!response.ok || !result.success) throw new Error(result.message || 'Enregistrement impossible.');
      if (status) status.textContent = 'Paramètres enregistrés.';
      showToast('Paramètres enregistrés', 'success');
      refreshMailStatus();
    } catch (error) {
      if (status) status.textContent = error.message;
      showToast(error.message, 'error');
    } finally {
      disabled.forEach(el => el.disabled = true);
    }
  });

  const smtpProfile = document.getElementById('smtpProfile');
  const smtpHost = document.getElementById('smtpHost');
  const smtpPort = form.querySelector('[name="smtp[port]"]');
  const smtpUsername = form.querySelector('[name="smtp[username]"]');
  const smtpSecurity = form.querySelector('[name="smtp[security]"]');
  const smtpProfiles = {
    hostinger: { host: 'smtp.hostinger.com', port: '587', username: '<?= e((string) ($appSettings['smtp']['username'] ?? '')) ?>', security: 'tls' },
    localhost: { host: '127.0.0.1', port: '25', username: '', security: 'none' }
  };
  const applySmtpProfile = () => {
    const profile = smtpProfile ? smtpProfile.value : 'custom';
    const preset = smtpProfiles[profile];
    if (preset) {
      if (smtpHost) smtpHost.value = preset.host;
      if (smtpPort) smtpPort.value = preset.port;
      if (smtpUsername) smtpUsername.value = preset.username;
      if (smtpSecurity) smtpSecurity.value = preset.security;
    }
    if (smtpHost) smtpHost.readOnly = Boolean(preset);
    if (smtpPort) smtpPort.readOnly = Boolean(preset);
    if (smtpUsername) smtpUsername.readOnly = profile === 'localhost';
    if (smtpSecurity) smtpSecurity.disabled = Boolean(preset);
  };
  if (smtpProfile) smtpProfile.addEventListener('change', applySmtpProfile);
  applySmtpProfile();

  const smtpTestButton = document.getElementById('smtpTestBtn');
  const smtpTestStatus = document.getElementById('smtpTestStatus');
  if (smtpTestButton) {
    smtpTestButton.addEventListener('click', async () => {
      if (smtpTestStatus) smtpTestStatus.textContent = 'Test en cours...';
      const data = new FormData();
      data.append('csrf_token', form.querySelector('[name="csrf_token"]').value);
      data.append('recipient', form.querySelector('[name="contact_email"]').value);
      try {
        const response = await fetch('<?= BASE_URL ?>/admin/email/test', { method: 'POST', body: data });
        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) throw new Error(result.message || 'Test email impossible.');
        if (smtpTestStatus) smtpTestStatus.textContent = result.message || 'Email de test envoyé.';
        showToast('Test email envoyé', 'success');
        refreshMailStatus();
      } catch (error) {
        if (smtpTestStatus) smtpTestStatus.textContent = error.message;
        showToast(error.message, 'error');
      }
    });
  }

  const importInput = document.getElementById('databaseImportFile');
  if (importInput) {
    importInput.addEventListener('change', async () => {
      if (!importInput.files.length) return;
      const importStatus = document.getElementById('databaseImportStatus');
      if (importStatus) importStatus.textContent = 'Import en cours...';
      const data = new FormData();
      data.append('csrf_token', form.querySelector('[name="csrf_token"]').value);
      data.append('database_file', importInput.files[0]);
      try {
        const response = await fetch('<?= BASE_URL ?>/admin/database/import', { method: 'POST', body: data });
        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) throw new Error(result.message || 'Import impossible.');
        if (importStatus) importStatus.textContent = result.message || 'Base importée.';
        showToast('Base importée', 'success');
        setTimeout(() => location.reload(), 900);
      } catch (error) {
        if (importStatus) importStatus.textContent = error.message;
        showToast(error.message, 'error');
      } finally {
        importInput.value = '';
      }
    });
  }

  refreshMailStatus();
}

async function refreshMailStatus() {
  const smtpConnectivityStatus = document.getElementById('smtpConnectivityStatus');
  if (!smtpConnectivityStatus) return;
  try {
    const response = await fetch('<?= BASE_URL ?>/admin/email/status');
    const result = await response.json();
    const state = result.online ? 'en ligne' : 'hors ligne';
    smtpConnectivityStatus.textContent = `Email ${state} - file: ${result.queue_count || 0}`;
  } catch (error) {
    smtpConnectivityStatus.textContent = 'Statut email indisponible.';
  }
}

function resetDashboardUserPermissions() {
  const form = document.getElementById('dashboardUserForm');
  if (!form) return;
  form.querySelectorAll('input[name^="permissions"]').forEach(input => { input.checked = false; });
}

function applyDashboardRoleDefaults(role) {
  const form = document.getElementById('dashboardUserForm');
  const defaults = (dashboardRoleDefaults[role] || {}).modules || {};
  Object.entries(defaults).forEach(([moduleKey, modulePerms]) => {
    Object.entries(modulePerms || {}).forEach(([action, enabled]) => {
      const input = form.querySelector(`input[name="permissions[modules][${moduleKey}][${action}]"]`);
      if (input) input.checked = Boolean(enabled);
    });
  });
}

function openDashboardUserModal(user = null) {
  const modal = document.getElementById('modalDashboardUser');
  const form = document.getElementById('dashboardUserForm');
  const title = document.getElementById('dashboardUserModalTitle');
  const status = document.getElementById('dashboardUserStatus');
  if (!modal || !form) return;
  const field = name => form.elements.namedItem(name);
  form.reset();
  resetDashboardUserPermissions();
  if (status) status.textContent = '';
  field('id').value = user && user.id ? user.id : '';
  field('name').value = user && user.name ? user.name : '';
  field('username').value = user && user.username ? user.username : '';
  field('email').value = user && user.email ? user.email : '';
  field('role').value = user && user.role ? user.role : 'user';
  field('is_active').checked = !user || Number(user.is_active) === 1;
  if (title) title.innerHTML = '<i class="fas fa-user-plus" style="color:var(--secondary);margin-right:8px"></i> ' + (user ? 'Modifier utilisateur' : 'Nouvel utilisateur');
  applyDashboardRoleDefaults(field('role').value);
  if (user && user.permissions) {
    try {
      const perms = typeof user.permissions === 'string' ? JSON.parse(user.permissions) : user.permissions;
      Object.entries((perms.modules || {})).forEach(([moduleKey, modulePerms]) => {
        Object.entries(modulePerms || {}).forEach(([action, enabled]) => {
          const input = form.querySelector(`input[name="permissions[modules][${moduleKey}][${action}]"]`);
          if (input) input.checked = Boolean(enabled);
        });
      });
    } catch (error) {}
  }
  modal.classList.add('open');
}

function closeDashboardUserModal() {
  const modal = document.getElementById('modalDashboardUser');
  if (modal) modal.classList.remove('open');
}

async function deleteDashboardUser(id) {
  if (!id || !confirm('Supprimer cet utilisateur ?')) return;
  const data = new FormData();
  data.append('csrf_token', '<?= e(csrf_token()) ?>');
  data.append('id', id);
  try {
    const response = await fetch('<?= BASE_URL ?>/admin/users/delete', { method: 'POST', body: data });
    const result = await response.json().catch(() => ({}));
    if (!response.ok || !result.success) throw new Error(result.message || 'Suppression impossible.');
    showToast('Utilisateur supprimé', 'success');
    location.reload();
  } catch (error) {
    showToast(error.message, 'error');
  }
}

function initDashboardUserForm() {
  const form = document.getElementById('dashboardUserForm');
  if (!form || form.dataset.bound === '1') return;
  form.dataset.bound = '1';
  const field = name => form.elements.namedItem(name);
  field('role').addEventListener('change', () => {
    resetDashboardUserPermissions();
    applyDashboardRoleDefaults(field('role').value);
  });
  form.addEventListener('submit', async event => {
    event.preventDefault();
    const status = document.getElementById('dashboardUserStatus');
    if (status) status.textContent = 'Enregistrement...';
    try {
      const response = await fetch('<?= BASE_URL ?>/admin/users', { method: 'POST', body: new FormData(form) });
      const result = await response.json().catch(() => ({}));
      if (!response.ok || !result.success) throw new Error(result.message || 'Enregistrement impossible.');
      showToast(result.message || 'Utilisateur enregistré', 'success');
      closeDashboardUserModal();
      location.reload();
    } catch (error) {
      if (status) status.textContent = error.message;
      showToast(error.message, 'error');
    }
  });
}

function getStoredModule() {
  try {
    return localStorage.getItem(DASHBOARD_MODULE_STORAGE_KEY);
  } catch(e) {
    return null;
  }
}

function storeModule(name) {
  try {
    localStorage.setItem(DASHBOARD_MODULE_STORAGE_KEY, name);
  } catch(e) {}
}

function switchModule(name, trigger = null) {
  if (!document.getElementById('module-' + name)) name = 'apercu';
  document.querySelectorAll('.dash-module').forEach(m => m.classList.remove('active'));
  document.getElementById('module-' + name).classList.add('active');
  storeModule(name);
  document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
  if (trigger && trigger.classList && trigger.classList.contains('sidebar-item')) {
    trigger.classList.add('active');
  } else {
    const sidebarItem = document.querySelector(`.sidebar-item[onclick*="'${name}'"]`);
    if (sidebarItem) sidebarItem.classList.add('active');
  }
  document.getElementById('breadcrumbCurrent').textContent = moduleLabels[name] || name;
  if (name === 'calendrier') setTimeout(initCalendar, 100);
  if (name === 'apercu') { initCharts(); animateKPIs(); }
  if (name === 'criteres') setTimeout(initCriteriaChart, 100);
}

// ══════════════════════════════════════
// CHARTS
// ══════════════════════════════════════
let chartsInit = false;
let chartInstances = {};

function initCharts() {
  if (chartsInit) return;
  chartsInit = true;
  Chart.defaults.color = 'rgba(255,255,255,0.4)';
  Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';

  // Submissions line chart
  const ctx1 = document.getElementById('chartSubmissions');
  if (ctx1) {
    chartInstances.submissions = new Chart(ctx1, {
      type: 'line',
      data: {
        labels: overviewData.monthly?.labels || [],
        datasets: [{
          label: 'Soumissions',
          data: overviewData.monthly?.totals || [],
          borderColor: '#2EAF7D',
          backgroundColor: 'rgba(46,175,125,0.08)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#2EAF7D',
          pointRadius: 4,
          pointHoverRadius: 7
        }, {
          label: 'Évaluées',
          data: (overviewData.monthly?.totals || []).map(value => Math.round(value * 0.7)),
          borderColor: '#F5A623',
          backgroundColor: 'rgba(245,166,35,0.05)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#F5A623',
          pointRadius: 4,
          pointHoverRadius: 7
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { labels: { color: 'rgba(255,255,255,0.5)', font: { size: 12 } } } },
        scales: {
          x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.4)' } },
          y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.4)' } }
        }
      }
    });
  }

  // Doughnut countries
  const ctx2 = document.getElementById('chartCountries');
  if (ctx2) {
    chartInstances.countries = new Chart(ctx2, {
      type: 'doughnut',
      data: {
        labels: (overviewData.countries || []).map(item => item.label),
        datasets: [{
          data: (overviewData.countries || []).map(item => item.total),
          backgroundColor: ['#2EAF7D', '#F5A623', '#3498db', '#9b59b6', '#e67e22'],
          borderWidth: 2,
          borderColor: '#1E1E2E'
        }]
      },
      options: {
        responsive: true,
        cutout: '65%',
        plugins: {
          legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.5)', padding: 12, font: { size: 12 } } }
        }
      }
    });
  }

  // Bar scores
  const ctx3 = document.getElementById('chartScores');
  if (ctx3) {
    chartInstances.scores = new Chart(ctx3, {
      type: 'bar',
      data: {
        labels: (overviewData.scores || []).map(item => item.label),
        datasets: [{
          label: 'Score moyen /20',
          data: (overviewData.scores || []).map(item => item.score),
          backgroundColor: ['rgba(46,175,125,0.8)', 'rgba(245,166,35,0.8)', 'rgba(52,152,219,0.8)', 'rgba(255,255,255,0.1)'],
          borderRadius: 6,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.4)' } },
          y: { max: 20, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.4)' } }
        }
      }
    });
  }
}

function initCriteriaChart() {
  const ctx = document.getElementById('chartCriteria');
  if (!ctx || ctx._chartInst) return;
  ctx._chartInst = true;
  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Pertinence', 'Faisabilité', 'Environnement', 'Financier', 'Social', 'Équipe'],
      datasets: [{ data: [2.0, 1.5, 1.5, 1.0, 1.0, 0.75], backgroundColor: ['#2EAF7D','#F5A623','#3498db','#9b59b6','#e67e22','#e74c3c'], borderWidth: 2, borderColor: '#252535' }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.5)', padding: 8, font: { size: 11 } } } } }
  });
}

// ══════════════════════════════════════
// KPI COUNTER ANIMATION
// ══════════════════════════════════════
function animateKPIs() {
  document.querySelectorAll('.kpi-val[data-target]').forEach(el => {
    const target = parseInt(el.getAttribute('data-target'));
    let current = parseInt(el.textContent, 10) || 0;
    if (current >= target) {
      el.textContent = target;
      return;
    }
    const duration = 1200;
    const step = target / (duration / 16);
    const interval = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = Math.round(current);
      if (current >= target) clearInterval(interval);
    }, 16);
  });
}

// ══════════════════════════════════════
// HERO STATS COUNTER
// ══════════════════════════════════════
function animateHeroStats() {
  const targets = { hstat1: 156, hstat2: 2847, hstat3: 38 };
  Object.entries(targets).forEach(([id, target]) => {
    const el = document.getElementById(id);
    let current = 0;
    const step = target / 80;
    const interval = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = Math.round(current);
      if (current >= target) clearInterval(interval);
    }, 20);
  });
}

function animateStats() {
  const targets = { stat1: 156, stat2: 2847, stat3: 48, stat4: 38 };
  Object.entries(targets).forEach(([id, target]) => {
    const el = document.getElementById(id);
    if (!el || el._animated) return;
    el._animated = true;
    let current = 0;
    const step = target / 80;
    const interval = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = Math.round(current);
      if (current >= target) clearInterval(interval);
    }, 20);
  });
}

// ══════════════════════════════════════
// EVAL SCORE REAL-TIME
// ══════════════════════════════════════
function updateEvalScore() {
  const s1 = parseFloat(document.getElementById('score1').value) || 0;
  const s2 = parseFloat(document.getElementById('score2').value) || 0;
  const s3 = parseFloat(document.getElementById('score3').value) || 0;
  const s4 = parseFloat(document.getElementById('score4').value) || 0;
  const weighted = (s1 * 2.0 + s2 * 1.5 + s3 * 1.5 + s4 * 1.0) / (2.0 + 1.5 + 1.5 + 1.0);
  const percent = (weighted / 20) * 100;
  const circumference = 351.9;
  const offset = circumference - (circumference * percent / 100);

  document.getElementById('evalRingCircle').setAttribute('stroke-dashoffset', offset);
  document.getElementById('evalScoreText').textContent = weighted.toFixed(1);
  ['score1','score2','score3','score4'].forEach((id, i) => {
    const pct = (parseFloat(document.getElementById(id).value) || 0) / 20 * 100;
    const fill = document.getElementById('fill-' + id);
    if (fill) fill.style.width = pct + '%';
  });
  const level = weighted >= 17 ? 'Excellent' : weighted >= 14 ? 'Très bien' : weighted >= 11 ? 'Bien' : weighted >= 8 ? 'Passable' : 'Insuffisant';
  document.getElementById('evalLevelText').textContent = level;
}

function handleSliderClick(clickEvent, track, scoreId) {
  const rect = track.getBoundingClientRect();
  const pct = (clickEvent.clientX - rect.left) / rect.width;
  const value = Math.round(pct * 40) / 2;
  document.getElementById(scoreId).value = Math.min(20, Math.max(0, value));
  updateEvalScore();
}

function showEvalForm() {
  document.getElementById('evalFormSection').classList.remove('hidden');
  document.getElementById('evalFormSection').scrollIntoView({ behavior: 'smooth' });
  updateEvalScore();
}

// ══════════════════════════════════════
// FORM TABS
// ══════════════════════════════════════
function switchFormTab(tab, btn) {
  ['galerie', 'builder', 'planning'].forEach(t => {
    const el = document.getElementById('formTab-' + t);
    if (el) el.classList.add('hidden');
  });
  const target = document.getElementById('formTab-' + tab);
  if (target) target.classList.remove('hidden');
  document.querySelectorAll('.tab-btn').forEach(b => {
    b.style.background = 'transparent';
    b.style.color = 'rgba(255,255,255,0.5)';
  });
  if (btn) { btn.style.background = 'var(--secondary)'; btn.style.color = '#fff'; }
  if (tab === 'planning') setTimeout(initCalendar, 100);
}

// ══════════════════════════════════════
// CALENDAR
// ══════════════════════════════════════
let calInit = false;
function initCalendar() {
  const calEl = document.getElementById('adminCalendar');
  if (!calEl || calInit) return;
  calInit = true;
  $(calEl).fullCalendar({
    locale: 'fr',
    header: { left: 'prev,next today', center: 'title', right: 'month,agendaWeek,listMonth' },
    events: [
      { title: 'AGRI-2025 — Ouverture', start: '2025-11-01', end: '2025-11-30', color: '#2EAF7D' },
      { title: 'AGRI-2025 — Clôture', start: '2025-12-15', color: '#E74C3C' },
      { title: 'Innov-Tech — Actif', start: '2025-10-15', end: '2025-12-20', color: '#F5A623' },
      { title: 'Bourses 2026 — Planifié', start: '2026-01-15', end: '2026-03-01', color: '#3498db' },
      { title: 'Fonds Santé — Clôturé', start: '2025-09-01', end: '2025-10-31', color: '#9b59b6' },
      { title: 'Comité d\'évaluation', start: '2026-01-05', color: '#e67e22' }
    ],
    eventClick: function(event) { showToast('Formulaire : ' + event.title, 'success'); },
    dayClick: function(date) { $('#modalPlanning').addClass('open'); }
  });
}

// ══════════════════════════════════════
// TOAST NOTIFICATIONS
// ══════════════════════════════════════
function showToast(msg, type = 'success') {
  const icons = { success: 'fa-check-circle', warning: 'fa-exclamation-triangle', error: 'fa-times-circle', info: 'fa-info-circle' };
  const colors = { success: '#2EAF7D', warning: '#F5A623', error: '#E74C3C', info: '#3498db' };
  type = icons[type] ? type : 'success';
  const toast = $(`<div class="toast ${type}"><i class="fas ${icons[type]} toast-icon" style="color:${colors[type]}"></i><span class="toast-text">${msg}</span></div>`);
  $('body').append(toast);
  setTimeout(() => toast.css({ opacity: 0, transform: 'translateX(20px)', transition: 'all 0.3s' }), 2500);
  setTimeout(() => toast.remove(), 2900);
}

// ══════════════════════════════════════
// CONFIRM DELETE
// ══════════════════════════════════════
function confirmDelete() { $('#modalDelete').addClass('open'); }

function openTrainingPanel(title) {
  const panel = document.getElementById('trainingDetailPanel');
  if (!panel) return;
  const subEl = panel.querySelector('.chart-sub');
  if (subEl) subEl.textContent = title || 'Formation sélectionnée';
  panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  showToast('Session de formation chargée', 'success');
}

function toggleTrainingStatus(btn) {
  const row = btn.closest('tr');
  const badge = row ? row.querySelector('.badge') : null;
  const icon = btn.querySelector('i');
  const isActive = badge && badge.textContent.trim() === 'Active';
  if (badge) {
    badge.className = isActive ? 'badge badge-warning' : 'badge badge-success';
    badge.textContent = isActive ? 'Brouillon' : 'Active';
  }
  if (icon) {
    icon.className = isActive ? 'fas fa-toggle-off' : 'fas fa-toggle-on';
  }
  showToast(isActive ? 'Formation désactivée' : 'Formation activée', isActive ? 'warning' : 'success');
}

function saveTraining(activate) {
  const titleInput = document.getElementById('trainingTitle');
  const title = titleInput && titleInput.value.trim() ? titleInput.value.trim() : 'Nouvelle formation';
  $('#modalTraining').removeClass('open');
  showToast(activate ? `Formation "${title}" créée et activée` : `Brouillon "${title}" enregistré`, activate ? 'success' : 'info');
}

// ══════════════════════════════════════
// SIDEBAR TOGGLE
// ══════════════════════════════════════
$('#sidebarToggle').on('click', function() {
  $('#sidebar').toggleClass('collapsed');
  $('#topbar').toggleClass('expanded');
  $('#mainContent').toggleClass('expanded');
});

// ══════════════════════════════════════
// CHART PERIOD BUTTONS
// ══════════════════════════════════════
$(document).on('click', '.chart-btn', function() {
  $(this).siblings().removeClass('active');
  $(this).addClass('active');
});

// ══════════════════════════════════════
// CRITERIA DRAG & DROP
// ══════════════════════════════════════
function initSortable() {
  if (!window.jQuery || typeof $.fn.sortable !== 'function') return;
  if ($('#criteriaList').length && !$('#criteriaList').hasClass('ui-sortable')) {
    $('#criteriaList').sortable({ handle: '.drag-handle', axis: 'y', animation: 150 });
  }
  if ($('#kpiSortable').length && !$('#kpiSortable').hasClass('ui-sortable')) {
    $('#kpiSortable').sortable({ handle: '.widget-handle', animation: 150, tolerance: 'pointer' });
  }
}

// ══════════════════════════════════════
// LANDING INIT
// ══════════════════════════════════════
function initLanding() {
  setTimeout(animateHeroStats, 500);

  // Scroll navbar
  $(window).on('scroll', function() {
    if ($(this).scrollTop() > 50) $('#pubNav').addClass('scrolled');
    else $('#pubNav').removeClass('scrolled');
  });

  // Stats counter observer
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) animateStats(); });
  }, { threshold: 0.3 });
  const statsSection = document.getElementById('stats');
  if (statsSection) observer.observe(statsSection);

  // Init Leaflet Africa map
  setTimeout(initAfricaMap, 200);
}

// ══════════════════════════════════════
// AFRICA MAP
// ══════════════════════════════════════
function initAfricaMap() {
  const mapEl = document.getElementById('africa-map');
  if (!mapEl || mapEl._mapInit) return;
  mapEl._mapInit = true;
  try {
    const map = L.map('africa-map', { zoomControl: true, scrollWheelZoom: false }).setView([5, 20], 3);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap'
    }).addTo(map);

    const africaCountries = [
      { name: 'Cameroun', latlng: [3.848, 11.502], count: 34 },
      { name: 'Côte d\'Ivoire', latlng: [7.540, -5.547], count: 28 },
      { name: 'Sénégal', latlng: [14.497, -14.452], count: 21 },
      { name: 'Mali', latlng: [17.570, -3.996], count: 18 },
      { name: 'Guinée', latlng: [11.788, -15.180], count: 12 },
      { name: 'Burkina Faso', latlng: [12.364, -1.535], count: 15 },
      { name: 'Nigeria', latlng: [9.082, 8.675], count: 22 },
      { name: 'Ghana', latlng: [7.946, -1.023], count: 11 },
    ];

    africaCountries.forEach(c => {
      const color = c.count > 25 ? '#2EAF7D' : c.count > 15 ? '#F5A623' : '#3498db';
      const circle = L.circleMarker(c.latlng, {
        radius: 8 + c.count / 5,
        fillColor: color,
        color: '#fff',
        weight: 2,
        opacity: 0.9,
        fillOpacity: 0.75
      }).addTo(map);
      circle.bindPopup(`<div style="font-family:'Inter',sans-serif"><strong>${c.name}</strong><br><span style="color:#2EAF7D">${c.count} candidatures</span></div>`);
    });
  } catch(e) { console.log('Map init error:', e); }
}

// ══════════════════════════════════════
// DASHBOARD INIT
// ══════════════════════════════════════
function initDashboard() {
  renderSidebarCounts();
  initSettingsPanel();
  initDashboardUserForm();
  const storedModule = getStoredModule();
  if (storedModule && document.getElementById('module-' + storedModule)) {
    switchModule(storedModule);
  } else {
    switchModule('apercu');
  }
  setTimeout(() => {
    initCharts();
    animateKPIs();
    initSortable();
  }, 200);
}

// ══════════════════════════════════════
// CANVAS ELEMENT SELECTION
// ══════════════════════════════════════
$(document).on('click', '.canvas-element', function() {
  $('.canvas-element').removeClass('selected');
  $(this).addClass('selected');
});

// ══════════════════════════════════════
// TOPBAR DATE PICKER
// ══════════════════════════════════════
$(function() {
  if ($('#daterange').length) {
    $('#daterange').val('Nov 2025 — Déc 2025');
  }

  // Init page
  initDashboard();
});

// ══════════════════════════════════════
// CHART PERIOD UPDATES
// ══════════════════════════════════════
$('.chart-btn').on('click', function() {
  const datasets6M = [12, 19, 28, 45, 67, 92];
  const datasets1A = [5, 8, 12, 19, 28, 45, 32, 67, 55, 78, 92, 110];
  if (chartInstances.submissions) {
    const data = $(this).text() === '6M' ? datasets6M : datasets1A;
    const labels = $(this).text() === '6M' ? ['Juil','Août','Sept','Oct','Nov','Déc'] : ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
    chartInstances.submissions.data.datasets[0].data = data;
    chartInstances.submissions.data.labels = labels;
    chartInstances.submissions.update();
  }
});
</script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script src="<?= BASE_URL ?>/assets/js/builder.js"></script>
<script>
  (function () {
    const initBuilder = function () {
      if (window.CritevalBuilder && document.getElementById('fbCanvasInner')) {
        try {
          window.CritevalBuilder.init({ canvas: 'fbCanvasInner', input: 'fb_layout_json' });
        } catch (e) {
          console.warn('Builder init failed', e);
        }
      }
    };

    const initCriteriaModule = function () {
      if (!document.getElementById('criteriaForm')) return;

      window.CRITEVAL_BASE_URL = window.CRITEVAL_BASE_URL || '<?= BASE_URL ?>';

      async function loadCriteriaList() {
        const projectFilter = document.getElementById('criteriaProjectFilter');
        const listEl = document.getElementById('criteriaList');
        const totalWeightEl = document.getElementById('criteriaTotalWeight');
        const countLabelEl = document.getElementById('criteriaCountLabel');
        if (!listEl) return;

        try {
          const response = await fetch(window.CRITEVAL_BASE_URL + '/admin/criteria', {
            headers: { Accept: 'application/json' }
          });
          const payload = await response.json();
          if (!response.ok || !payload.success) throw new Error(payload.message || 'Erreur de chargement');

          let items = Array.isArray(payload.items) ? payload.items : [];
          if (projectFilter && projectFilter.value) {
            items = items.filter(item => String(item.project_id) === String(projectFilter.value));
          }

          listEl.innerHTML = items.length
            ? items.map((item, index) => `
                <div class="criteria-item">
                  <i class="fas fa-grip-vertical drag-handle"></i>
                  <div class="criteria-num">${String(index + 1).padStart(2, '0')}</div>
                  <div class="criteria-info">
                    <div class="criteria-label">${(item.label || 'Critère sans libellé')}</div>
                    <div class="criteria-desc">${item.description || 'Aucune description'}</div>
                  </div>
                  <div class="criteria-weights">
                    <span class="weight-pill">×${Number(item.weight || 1).toFixed(2)}</span>
                    <span class="score-pill">/${Number(item.max_score || 20).toFixed(0)}</span>
                  </div>
                  <div class="project-card-actions">
                    <button type="button" class="btn btn-icon btn-ghost-dark" title="Modifier" onclick='editCriterion(${JSON.stringify(item).replace(/'/g, "&#39;")})'><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-icon" title="Supprimer" style="background:rgba(231,76,60,0.1);color:var(--danger)" onclick="deleteCriterion(${Number(item.id)})"><i class="fas fa-trash"></i></button>
                  </div>
                </div>
              `).join('')
            : '<div class="criteria-item"><div class="criteria-info"><div class="criteria-label">Aucun critère pour cette organisation.</div><div class="criteria-desc">Ajoutez un nouveau critère sur la droite.</div></div></div>';

          const total = items.reduce((sum, item) => sum + Number(item.weight || 0), 0);
          if (totalWeightEl) totalWeightEl.textContent = total.toFixed(2);
          if (countLabelEl) countLabelEl.textContent = `${items.length} critère(s) défini(s)`;
        } catch (error) {
          listEl.innerHTML = '<div class="criteria-item"><div class="criteria-info"><div class="criteria-label">Impossible de charger les critères.</div><div class="criteria-desc">' + (error.message || 'Erreur inconnue') + '</div></div></div>';
        }
      }

      const criteriaForm = document.getElementById('criteriaForm');
      window.deleteCriterion = async function (criteriaId) {
        if (!window.confirm('Supprimer définitivement ce critère ?')) return;
        const formData = new FormData();
        formData.append('id', String(criteriaId));
        formData.append('csrf_token', criteriaForm.querySelector('input[name="csrf_token"]').value);
        try {
          const response = await fetch(window.CRITEVAL_BASE_URL + '/admin/criteria/delete', { method: 'POST', body: formData });
          const payload = await response.json();
          if (!response.ok || !payload.success) throw new Error(payload.message || 'Suppression impossible.');
          showToast(payload.message, 'success');
          loadCriteriaList();
        } catch (error) {
          showToast(error.message || 'Suppression impossible.', 'error');
        }
      };

      let editingCriterionId = 0;
      const criteriaFormTitle = document.getElementById('criteriaFormTitle');
      const criteriaFormIcon = document.getElementById('criteriaFormIcon');
      const criteriaFormSubmit = document.getElementById('criteriaFormSubmit');
      const criteriaFormCancel = document.getElementById('criteriaFormCancel');

      function resetCriteriaForm() {
        editingCriterionId = 0;
        criteriaForm.reset();
        criteriaForm.querySelector('input[name="is_required"]').checked = true;
        criteriaFormTitle.textContent = 'Nouveau critère';
        criteriaFormIcon.className = 'fas fa-plus';
        criteriaFormSubmit.querySelector('span').textContent = 'Enregistrer';
        criteriaFormCancel.classList.add('hidden');
      }

      window.editCriterion = function (criterion) {
        editingCriterionId = Number(criterion.id);
        criteriaForm.elements.project_id.value = String(criterion.project_id || '');
        criteriaForm.elements.label.value = criterion.label || '';
        criteriaForm.elements.description.value = criterion.description || '';
        criteriaForm.elements.weight.value = criterion.weight || 1;
        criteriaForm.elements.max_score.value = criterion.max_score || 20;
        criteriaForm.elements.is_required.checked = Boolean(Number(criterion.is_required));
        criteriaFormTitle.textContent = 'Modifier le critère';
        criteriaFormIcon.className = 'fas fa-edit';
        criteriaFormSubmit.querySelector('span').textContent = 'Mettre à jour';
        criteriaFormCancel.classList.remove('hidden');
        criteriaForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        criteriaForm.elements.label.focus();
      };

      criteriaFormCancel.addEventListener('click', resetCriteriaForm);

      if (criteriaForm) {
        criteriaForm.addEventListener('submit', async function (event) {
          event.preventDefault();
          const formData = new FormData(criteriaForm);
          const endpoint = editingCriterionId ? '/admin/criteria/update' : '/admin/criteria';
          if (editingCriterionId) formData.append('id', String(editingCriterionId));
          const response = await fetch(window.CRITEVAL_BASE_URL + endpoint, { method: 'POST', body: formData });
          const payload = await response.json();

          if (!response.ok || !payload.success) {
            showToast(payload.message || 'Erreur lors de l\'enregistrement du critère', 'error');
            return;
          }

          showToast(editingCriterionId ? 'Critère modifié avec succès' : 'Critère enregistré avec succès', 'success');
          resetCriteriaForm();
          loadCriteriaList();
        });
      }

      const projectFilter = document.getElementById('criteriaProjectFilter');
      if (projectFilter) {
        projectFilter.addEventListener('change', loadCriteriaList);
      }

      loadCriteriaList();
    };

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      initBuilder();
      initCriteriaModule();
    } else {
      window.addEventListener('DOMContentLoaded', function () {
        initBuilder();
        initCriteriaModule();
      });
    }
  })();
</script>

</body>
</html>
