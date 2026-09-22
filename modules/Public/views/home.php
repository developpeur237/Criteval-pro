<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Criteval Pro - Évaluation des organisations</title>

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

.projects-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.project-card {
  background: var(--neutral-dark2); border: 1px solid var(--border-dark); border-radius: var(--radius);
  overflow: hidden; transition: all 0.3s; cursor: pointer;
}
.project-card:hover { border-color: rgba(46,175,125,0.4); transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
.project-card-img {
  height: 140px; position: relative; overflow: hidden;
  display: flex; align-items: center; justify-content: center;
}
.project-card-img .proj-icon { font-size: 40px; opacity: 0.6; }
.project-card-body { padding: 20px; }
.project-card-meta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.project-card-title { color: #fff; font-weight: 600; font-size: 15px; margin-bottom: 6px; font-family: 'Poppins', sans-serif; }
.project-card-org { color: rgba(255,255,255,0.4); font-size: 13px; display: flex; align-items: center; gap: 6px; margin-bottom: 14px; }
.project-card-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.06); }
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
  color: rgba(255,255,255,0.8); font-size: 14px; padding: 9px 14px; outline: none; font-family: 'Inter', sans-serif;
}
.form-input-dark:focus { border-color: var(--secondary); }

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
<div id="page-landing" class="page active">
  <!-- NAV -->
  <nav class="pub-nav" id="pubNav">
    <div class="nav-logo">
      <div class="logo-icon"><i class="fas fa-chart-line"></i></div>
      <span>Criteval<em>_pro</em></span>
    </div>
    <ul class="nav-links">
      <li><a href="#avantages">Avantages</a></li>
      <li><a href="#fonctionnement">Fonctionnement</a></li>
      <li><a href="#carte">Couverture</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>
    <div style="display:flex;gap:10px;align-items:center;">
      <button class="btn btn-ghost btn-sm" onclick="showPage('login')">
        <i class="fas fa-lock"></i> Connexion Admin
      </button>
      <button class="btn btn-secondary btn-sm" onclick="showPage('candidate')">
        <i class="fas fa-file-alt"></i> Espace Candidat
      </button>
    </div>
  </nav>

  <!-- HERO -->
  <section class="pub-hero">
    <div class="hero-bg-pattern"></div>
    <div class="hero-glow"></div>
    <div class="hero-content">
      <div class="animate-fadeInUp">
        <div class="hero-eyebrow">
          <i class="fas fa-globe-africa"></i> Conçu pour l'Afrique
        </div>
        <h1 class="hero-title">
          L'évaluation des organisations,<br><span class="accent">réinventée</span><br>pour l'Afrique
        </h1>
        <p class="hero-sub">Criteval_pro centralise vos organisations, structure vos critères et publie vos classements en toute transparence.</p>
        <div class="hero-cta">
          <button class="btn btn-secondary btn-lg" onclick="showPage('login')">
            <i class="fas fa-rocket"></i> Demander une démo
          </button>
          <button class="btn btn-lg" style="background:rgba(255,255,255,0.1);color:#fff;border:1.5px solid rgba(255,255,255,0.2);" onclick="document.getElementById('avantages').scrollIntoView({behavior:'smooth'})">
            <i class="fas fa-play-circle"></i> Voir les fonctionnalités
          </button>
        </div>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-num" id="hstat1">0</div>
            <div class="hero-stat-label">Organisations évaluées</div>
          </div>
          <div>
            <div class="hero-stat-num" id="hstat2">0</div>
            <div class="hero-stat-label">Candidatures traitées</div>
          </div>
          <div>
            <div class="hero-stat-num" id="hstat3">0</div>
            <div class="hero-stat-label">Pays africains</div>
          </div>
        </div>
      </div>
      <div class="hero-visual animate-fadeInUp anim-delay-3">
        <div class="dashboard-preview" id="heroDashboard">
          <div class="dp-header">
            <div class="dp-dots">
              <div class="dp-dot" style="background:#E74C3C"></div>
              <div class="dp-dot" style="background:#F5A623"></div>
              <div class="dp-dot" style="background:#2EAF7D"></div>
            </div>
            <div style="flex:1;height:8px;background:rgba(255,255,255,0.05);border-radius:4px;margin:0 16px"></div>
            <div style="color:#555;font-size:11px">criteval.pro/admin</div>
          </div>
          <div class="dp-kpis">
            <div class="dp-kpi"><div class="dp-kpi-val" style="color:#2EAF7D">24</div><div class="dp-kpi-label">Organisations actives</div></div>
            <div class="dp-kpi"><div class="dp-kpi-val" style="color:#F5A623">187</div><div class="dp-kpi-label">Candidatures</div></div>
            <div class="dp-kpi"><div class="dp-kpi-val" style="color:#fff">92%</div><div class="dp-kpi-label">Évaluées</div></div>
          </div>
          <div class="dp-chart-area">
            <div style="color:#555;font-size:10px;margin-bottom:6px">Soumissions par mois</div>
            <div class="dp-chart-bar" id="heroBarChart">
              <div class="dp-bar" style="height:30%"></div>
              <div class="dp-bar" style="height:55%"></div>
              <div class="dp-bar" style="height:45%"></div>
              <div class="dp-bar" style="height:70%"></div>
              <div class="dp-bar" style="height:60%"></div>
              <div class="dp-bar" style="height:90%"></div>
              <div class="dp-bar" style="height:75%"></div>
              <div class="dp-bar" style="height:100%"></div>
            </div>
          </div>
          <div class="dp-row">
            <div class="dp-card-mini">
              <div style="color:#555;font-size:10px;margin-bottom:6px">Score moyen</div>
              <svg class="dp-score-ring" viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" fill="none" stroke="#252535" stroke-width="4"/>
                <circle cx="25" cy="25" r="20" fill="none" stroke="#2EAF7D" stroke-width="4" stroke-dasharray="113" stroke-dashoffset="28" transform="rotate(-90 25 25)"/>
                <text x="25" y="25" text-anchor="middle" dy="0.35em" fill="#fff" font-size="10" font-weight="700">75%</text>
              </svg>
            </div>
            <div class="dp-card-mini">
              <div style="color:#555;font-size:10px;margin-bottom:6px">Top pays</div>
              <div style="font-size:11px;color:#888">🇨🇲 Cameroun <span style="color:#2EAF7D">34</span></div>
              <div style="font-size:11px;color:#888;margin-top:3px">🇨🇮 Côte d'Ivoire <span style="color:#F5A623">28</span></div>
              <div style="font-size:11px;color:#888;margin-top:3px">🇸🇳 Sénégal <span style="color:#fff">21</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- AVANTAGES -->
  <section id="avantages" style="padding:100px 40px;background:#fff;">
    <div style="max-width:1200px;margin:0 auto;">
      <div class="section-eyebrow">Pourquoi Criteval_pro ?</div>
      <h2 class="section-title">Tout ce dont vous avez besoin<br>pour évaluer avec <span style="color:var(--secondary)">rigueur</span></h2>
      <div class="advantages-grid">
        <div class="adv-card animate-fadeInUp">
          <div class="adv-icon" style="background:var(--secondary-light);color:var(--secondary)"><i class="fas fa-sliders-h"></i></div>
          <h3 class="adv-title">Critères paramétrables</h3>
          <p class="adv-text">Définissez vos propres critères d'évaluation avec pondération, scores max et ordre personnalisable par glisser-déposer.</p>
        </div>
        <div class="adv-card animate-fadeInUp anim-delay-1">
          <div class="adv-icon" style="background:var(--primary-light);color:var(--primary)"><i class="fas fa-paint-brush"></i></div>
          <h3 class="adv-title">Formulaires WYSIWYG</h3>
          <p class="adv-text">Construisez des formulaires de candidature visuellement, élément par élément, sans coder. Exportables en PDF.</p>
        </div>
        <div class="adv-card animate-fadeInUp anim-delay-2">
          <div class="adv-icon" style="background:var(--accent-light);color:var(--accent)"><i class="fas fa-balance-scale"></i></div>
          <h3 class="adv-title">Notation transparente</h3>
          <p class="adv-text">Chaque évaluateur note sur des critères précis. Les scores sont pondérés automatiquement. Traçabilité complète.</p>
        </div>
        <div class="adv-card animate-fadeInUp anim-delay-3">
          <div class="adv-icon" style="background:#e74c3c20;color:var(--danger)"><i class="fas fa-trophy"></i></div>
          <h3 class="adv-title">Classements en temps réel</h3>
          <p class="adv-text">Les classements se mettent à jour à chaque nouvelle évaluation. Publiez les résultats en un clic.</p>
        </div>
        <div class="adv-card animate-fadeInUp anim-delay-4">
          <div class="adv-icon" style="background:var(--secondary-light);color:var(--secondary)"><i class="fas fa-globe-africa"></i></div>
          <h3 class="adv-title">Multi-pays africains</h3>
          <p class="adv-text">Gérez des programmes d’accompagnement d’organisations couvrant tout ou partie du continent africain, avec restriction géographique par pays.</p>
        </div>
        <div class="adv-card animate-fadeInUp anim-delay-5">
          <div class="adv-icon" style="background:var(--primary-light);color:var(--primary)"><i class="fas fa-file-pdf"></i></div>
          <h3 class="adv-title">Exports PDF & CSV</h3>
          <p class="adv-text">Générez des rapports d'évaluation, formulaires remplis, et classements complets en PDF officiel ou CSV.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="timeline-section" id="fonctionnement">
    <div class="timeline-inner">
      <div style="text-align:center;margin-bottom:20px">
        <div class="section-eyebrow" style="color:#5dd5a8">Comment ça marche ?</div>
        <h2 class="section-title" style="color:#fff">4 étapes simples vers vos résultats</h2>
      </div>
      <div class="timeline-steps">
        <div class="timeline-step">
          <div class="step-num">1</div>
          <h3 class="step-title">Créer le programme d’organisation</h3>
          <p class="step-desc">Définissez le cadre d’évaluation, son objectif et ses critères pondérés pour les organisations.</p>
        </div>
        <div class="timeline-step">
          <div class="step-num">2</div>
          <h3 class="step-title">Publier le formulaire</h3>
          <p class="step-desc">Construisez votre formulaire WYSIWYG et planifiez sa période d'ouverture aux candidats.</p>
        </div>
        <div class="timeline-step">
          <div class="step-num">3</div>
          <h3 class="step-title">Réceptionner les dossiers</h3>
          <p class="step-desc">Les candidats accèdent via OTP email et soumettent leurs candidatures en ligne.</p>
        </div>
        <div class="timeline-step">
          <div class="step-num">4</div>
          <h3 class="step-title">Évaluer & Classer</h3>
          <p class="step-desc">Notez chaque critère, calculez les scores pondérés et publiez le classement officiel.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- MAP -->
  <section class="map-section" id="carte">
    <div class="map-section-inner">
      <div>
        <div class="section-eyebrow">Couverture africaine</div>
        <h2 class="section-title">Pensé pour le<br>continent africain</h2>
        <p class="section-sub">Criteval_pro supporte tous les pays africains avec restriction géographique des formulaires, gestion multilingue et adaptation aux contextes locaux.</p>
        <div style="margin-top:32px;display:flex;flex-direction:column;gap:14px">
          <div style="display:flex;align-items:center;gap:12px;"><div style="width:10px;height:10px;border-radius:50%;background:var(--secondary);flex-shrink:0"></div><span style="color:var(--text-muted);font-size:14px">54 pays africains pris en charge</span></div>
          <div style="display:flex;align-items:center;gap:12px;"><div style="width:10px;height:10px;border-radius:50%;background:var(--accent);flex-shrink:0"></div><span style="color:var(--text-muted);font-size:14px">Restriction géographique par formulaire</span></div>
          <div style="display:flex;align-items:center;gap:12px;"><div style="width:10px;height:10px;border-radius:50%;background:var(--primary);flex-shrink:0"></div><span style="color:var(--text-muted);font-size:14px">Données en français et en anglais</span></div>
        </div>
        <button class="btn btn-primary" style="margin-top:28px" onclick="showPage('login')"><i class="fas fa-arrow-right"></i> Commencer</button>
      </div>
      <div id="africa-map"></div>
    </div>
  </section>

  <!-- STATS -->
  <section class="stats-section" id="stats">
    <div class="stats-inner">
      <div class="stat-item">
        <div class="stat-num" id="stat1">0</div>
        <div class="stat-label">Organisations créées</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat2">0</div>
        <div class="stat-label">Candidatures évaluées</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat3">0</div>
        <div class="stat-label">Organisations partenaires</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat4">0</div>
        <div class="stat-label">Pays africains couverts</div>
      </div>
    </div>
  </section>

  <!-- CTA FINAL -->
  <section class="cta-final">
    <h2>Prêt à lancer votre premier programme d’évaluation ?</h2>
    <p>Rejoignez les organisations africaines qui font confiance à Criteval Pro pour un processus d’évaluation transparent.</p>
    <button class="btn btn-lg" style="background:#fff;color:var(--secondary);font-weight:700" onclick="showPage('login')">
      <i class="fas fa-rocket"></i> Commencer maintenant — C'est gratuit
    </button>
  </section>

  <!-- FOOTER -->
  <footer class="pub-footer" id="contact">
    <div class="footer-grid">
      <div class="footer-brand">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
          <div style="width:32px;height:32px;background:var(--secondary);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px"><i class="fas fa-chart-line"></i></div>
          <span style="font-family:'Poppins',sans-serif;font-size:18px;font-weight:700;color:#fff">Criteval<em style="color:var(--secondary);font-style:normal">_pro</em></span>
        </div>
        <p>Plateforme SaaS d’évaluation des organisations, de leurs réalisations et de leurs preuves, conçue pour les réseaux africains.</p>
        <div style="display:flex;gap:12px;margin-top:20px">
          <a href="#" style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.5);text-decoration:none;transition:all 0.2s" onmouseover="this.style.background='var(--secondary)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.08)';this.style.color='rgba(255,255,255,0.5)'"><i class="fab fa-twitter"></i></a>
          <a href="#" style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.5);text-decoration:none" onmouseover="this.style.background='var(--secondary)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.08)';this.style.color='rgba(255,255,255,0.5)'"><i class="fab fa-linkedin"></i></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Produit</h4>
        <a href="#">Fonctionnalités</a>
        <a href="#">Tarifs</a>
        <a href="#">Cas d'usage</a>
        <a href="#">Feuille de route</a>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <a href="#">Documentation</a>
        <a href="#">Tutoriels</a>
        <a href="#">API Reference</a>
        <a href="#">Contact</a>
      </div>
      <div class="footer-col">
        <h4>Légal</h4>
        <a href="#">Politique de confidentialité</a>
        <a href="#">Conditions d'utilisation</a>
        <a href="#">Mentions légales</a>
        <a href="#">RGPD</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 Criteval_pro. Tous droits réservés. Développé pour l'Afrique.</p>
      <p style="display:flex;align-items:center;gap:8px"><span style="width:8px;height:8px;border-radius:50%;background:#2EAF7D;display:inline-block"></span> Tous les systèmes opérationnels</p>
    </div>
  </footer>
</div><!-- /page-landing -->

<script>
// ══════════════════════════════════════
// PAGE SYSTEM
// ══════════════════════════════════════
function showPage(name) {
  const targetPage = document.getElementById('page-' + name);
  if (!targetPage) {
    const routes = { landing: '<?= BASE_URL ?>/', login: '<?= BASE_URL ?>/login', dashboard: '<?= BASE_URL ?>/dashboard', candidate: '<?= BASE_URL ?>/candidate' };
    window.location.href = routes[name] || '<?= BASE_URL ?>/' + name;
    return;
  }
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  targetPage.classList.add('active');
  if (name === 'landing') initLanding();
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
  projets: 'Organisations',
  criteres: 'Critères d\'évaluation',
  formulaires: 'Formulaires',
  evaluations: 'Évaluations',
  classements: 'Classements',
  calendrier: 'Planning',
  parametres: 'Paramètres'
};
function switchModule(name) {
  document.querySelectorAll('.dash-module').forEach(m => m.classList.remove('active'));
  document.getElementById('module-' + name).classList.add('active');
  document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
  event && event.currentTarget && event.currentTarget.classList.add('active');
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
        labels: ['Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'],
        datasets: [{
          label: 'Soumissions',
          data: [12, 19, 28, 45, 67, 92],
          borderColor: '#2EAF7D',
          backgroundColor: 'rgba(46,175,125,0.08)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#2EAF7D',
          pointRadius: 4,
          pointHoverRadius: 7
        }, {
          label: 'Évaluées',
          data: [10, 15, 22, 38, 54, 80],
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
        labels: ['Cameroun', 'Côte d\'Ivoire', 'Sénégal', 'Mali', 'Autres'],
        datasets: [{
          data: [34, 28, 21, 18, 16],
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
        labels: ['AGRI-25', 'Innov-Tech', 'Santé', 'Bourses'],
        datasets: [{
          label: 'Score moyen /20',
          data: [14.8, 15.6, 13.2, 0],
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
    let current = 0;
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

function handleSliderClick(track, scoreId) {
  const rect = track.getBoundingClientRect();
  const pct = (event.clientX - rect.left) / rect.width;
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
  const icons = { success: 'fa-check-circle', warning: 'fa-exclamation-triangle', error: 'fa-times-circle' };
  const colors = { success: '#2EAF7D', warning: '#F5A623', error: '#E74C3C' };
  const toast = $(`<div class="toast ${type}"><i class="fas ${icons[type]} toast-icon" style="color:${colors[type]}"></i><span class="toast-text">${msg}</span></div>`);
  $('body').append(toast);
  setTimeout(() => toast.css({ opacity: 0, transform: 'translateX(20px)', transition: 'all 0.3s' }), 2500);
  setTimeout(() => toast.remove(), 2900);
}

// ══════════════════════════════════════
// CONFIRM DELETE
// ══════════════════════════════════════
function confirmDelete() { $('#modalDelete').addClass('open'); }

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
  initLanding();
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
</body>
</html>
