if (window.__CRITEVAL_APP_JS_LOADED__) {
  // Prevent the shared bundle from re-declaring globals when a page includes it more than once.
} else {
  window.__CRITEVAL_APP_JS_LOADED__ = true;

// ══════════════════════════════════════
// PAGE SYSTEM
// ══════════════════════════════════════
function navigateTo(page) {
  const baseUrl = window.CRITEVAL_BASE_URL || '';
  const routes = {
    'index.html': baseUrl + '/',
    'login.html': baseUrl + '/login',
    'dashboard.html': baseUrl + '/dashboard',
    'candidate.html': baseUrl + '/candidate',
    landing: baseUrl + '/',
    login: baseUrl + '/login',
    dashboard: baseUrl + '/dashboard',
    candidate: baseUrl + '/candidate'
  };
  window.location.href = routes[page] || page;
}

window.CritevalMail = window.CritevalMail || {
  async send({ to, subject, message, html = false, csrfToken = '' }) {
    const baseUrl = window.CRITEVAL_BASE_URL || '';
    const data = new FormData();
    const recipients = Array.isArray(to) ? to.join(',') : (to || '');
    data.append('to', recipients);
    data.append('subject', subject || '');
    data.append('message', message || '');
    data.append('html', html ? '1' : '0');
    data.append('csrf_token', csrfToken || document.querySelector('input[name="csrf_token"]')?.value || '');

    const response = await fetch(baseUrl + '/admin/email/send', { method: 'POST', body: data });
    const result = await response.json().catch(() => ({ success: false, message: 'Reponse serveur invalide.' }));
    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Email non envoye.');
    }
    return result;
  }
};

// ══════════════════════════════════════
// MODULE SWITCHING
// ══════════════════════════════════════
const moduleLabels = {
  apercu: 'Aperçu',
  projets: 'Organisations',
  criteres: 'Critères d\'évaluation',
  formulaires: 'Formulaires',
  formation: 'Formation',
  evaluations: 'Évaluations',
  classements: 'Classements',
  calendrier: 'Planning',
  parametres: 'Paramètres'
};

const moduleRoutes = {
  apercu: 'dashboard',
  projets: 'organisations',
  criteres: 'criteres',
  formulaires: 'formulaires',
  formation: 'formation',
  evaluations: 'evaluations',
  classements: 'classements',
  calendrier: 'planning',
  parametres: 'parametres'
};

function switchModule(name, trigger = null, updateHistory = true) {
  if (!document.getElementById('module-' + name)) name = 'apercu';
  document.querySelectorAll('.dash-module').forEach(m => m.classList.remove('active'));
  const module = document.getElementById('module-' + name);
  if (module) module.classList.add('active');
  
  document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
  const activeTrigger = trigger || window.event?.currentTarget;
  if (activeTrigger) activeTrigger.classList.add('active');

  const route = moduleRoutes[name] || moduleRoutes.apercu;
  const targetUrl = (window.CRITEVAL_BASE_URL || '') + '/' + route;
  if (updateHistory && window.location.pathname !== targetUrl) {
    window.history.pushState({ module: name }, '', targetUrl);
  }
  try { window.localStorage.setItem('criteval_module', name); } catch (error) {}
  
  const breadcrumb = document.getElementById('breadcrumbCurrent');
  if (breadcrumb) breadcrumb.textContent = moduleLabels[name] || name;
  
  if (name === 'calendrier') setTimeout(initCalendar, 100);
  if (name === 'apercu') { initCharts(); animateKPIs(); }
  if (name === 'criteres') setTimeout(initCriteriaChart, 100);
}

function bindUserWorkflow() {
  // Dashboard-specific user handlers are wired in the page template.
  // Keep this function defined so initDashboard can run safely.
}

// ══════════════════════════════════════
// CHARTS
// ══════════════════════════════════════
let chartsInit = false;
let chartInstances = {};

function initCharts() {
  if (window.__CRITEVAL_DASHBOARD__) return;
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
        labels: [],
        datasets: [{
          label: 'Soumissions',
          data: [],
          borderColor: '#2EAF7D',
          backgroundColor: 'rgba(46,175,125,0.08)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#2EAF7D',
          pointRadius: 4,
          pointHoverRadius: 7
        }, {
          label: 'Évaluées',
          data: [],
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
        labels: [],
        datasets: [{
          data: [],
          backgroundColor: ['#2EAF7D', '#F5A623', '#3498db', '#9b59b6', '#e67e22'],
          borderWidth: 2,
          borderColor: '#1E1E2E'
        }]
      },
      options: {
        responsive: true,
        cutout: '65%',
        plugins: { legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.5)', padding: 12, font: { size: 12 } } } }
      }
    });
  }

  // Bar scores
  const ctx3 = document.getElementById('chartScores');
  if (ctx3) {
    chartInstances.scores = new Chart(ctx3, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [{
          label: 'Score moyen /20',
          data: [],
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
      labels: [],
      datasets: [{ data: [], backgroundColor: ['#2EAF7D','#F5A623','#3498db','#9b59b6','#e67e22','#e74c3c'], borderWidth: 2, borderColor: '#252535' }]
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
    if (!el) return;
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
  const s1 = parseFloat(document.getElementById('score1')?.value) || 0;
  const s2 = parseFloat(document.getElementById('score2')?.value) || 0;
  const s3 = parseFloat(document.getElementById('score3')?.value) || 0;
  const s4 = parseFloat(document.getElementById('score4')?.value) || 0;
  const weighted = (s1 * 2.0 + s2 * 1.5 + s3 * 1.5 + s4 * 1.0) / (2.0 + 1.5 + 1.5 + 1.0);
  const percent = (weighted / 20) * 100;
  const circumference = 351.9;
  const offset = circumference - (circumference * percent / 100);

  const ring = document.getElementById('evalRingCircle');
  if (ring) ring.setAttribute('stroke-dashoffset', offset);
  
  const scoreText = document.getElementById('evalScoreText');
  if (scoreText) scoreText.textContent = weighted.toFixed(1);
  
  ['score1','score2','score3','score4'].forEach((id) => {
    const pct = (parseFloat(document.getElementById(id)?.value) || 0) / 20 * 100;
    const fill = document.getElementById('fill-' + id);
    if (fill) fill.style.width = pct + '%';
  });
  
  const level = weighted >= 17 ? 'Excellent' : weighted >= 14 ? 'Très bien' : weighted >= 11 ? 'Bien' : weighted >= 8 ? 'Passable' : 'Insuffisant';
  const levelText = document.getElementById('evalLevelText');
  if (levelText) levelText.textContent = level;
}

function showEvalForm() {
  const section = document.getElementById('evalFormSection');
  if (section) {
    section.classList.remove('hidden');
    section.scrollIntoView({ behavior: 'smooth' });
    updateEvalScore();
  }
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
    events: [],
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
// LIVE TUTORIAL SYSTEM
// ══════════════════════════════════════
window.CritevalTutorial = window.CritevalTutorial || {};

(function() {
  const tutorialState = {
    visible: false,
    page: null,
    steps: [],
    index: 0,
    overlay: null,
    highlight: null,
    popover: null,
    launchButton: null,
    step: null
  };

  const appWorkflowVersion = window.CritevalTutorial.workflowVersion || 'v2.1-workflow';
  const workflowHistory = Array.isArray(window.CritevalTutorial.majorChanges) && window.CritevalTutorial.majorChanges.length
    ? window.CritevalTutorial.majorChanges
    : [
        { id: 'workflow-2026-09-22', label: 'Organisation des parcours candidature, dashboard et analyses réelles', date: '2026-09-22' },
        { id: 'workflow-2026-09-22-country', label: 'Datalist pays depuis les organisations enregistrées', date: '2026-09-22' }
      ];

  const defaultTutorials = {
    landing: [
      { selector: '.pub-nav', title: 'Navigation principale', text: 'Commencez par la navigation globale pour accéder aux sections clés du parcours public.', position: 'bottom' },
      { selector: '.hero-title', title: 'Message de valeur', text: 'Cette zone présente l’objectif général de la plateforme et le bénéfice pour les organisations.', position: 'left' },
      { selector: '.hero-cta', title: 'Actions principales', text: 'Les boutons ci-dessous permettent d’accéder à la candidature ou à la consultation du parcours.', position: 'bottom' },
      { selector: '.timeline-step:nth-child(1)', title: 'Parcours en 4 étapes', text: 'Chaque étape décrit un bloc fonctionnel du processus de candidature et d’évaluation.', position: 'top' },
      { selector: '.adv-card:nth-child(1)', title: 'Avantages clés', text: 'Cette section met en avant les bénéfices offerts aux organisations et aux candidats.', position: 'left' }
    ],
    login: [
      { selector: '.auth-logo', title: 'Connexion à la plateforme', text: 'Bienvenue sur le portail sécurisé. Cette étape vous aide à identifier votre compte.', position: 'bottom' },
      { selector: 'input[type="email"], input[name="email"], input[name="login"]', title: 'Identifiant', text: 'Saisissez votre adresse email ou votre identifiant de connexion.', position: 'right' },
      { selector: 'input[type="password"], input[name="password"]', title: 'Mot de passe', text: 'Entrez votre mot de passe pour accéder à votre espace.', position: 'right' },
      { selector: 'button[type="submit"], .btn-primary', title: 'Valider la session', text: 'Cliquez ici pour ouvrir le tableau de bord et démarrer votre parcours.', position: 'top' }
    ],
    dashboard: [
      { selector: '.sidebar-item.active, .sidebar-item:nth-child(1)', title: 'Menu principal', text: 'Le menu latéral centralise les modules de gestion et de suivi.', position: 'right' },
      { selector: '#trainingSessionSelect', title: 'Sélecteur de session', text: 'Choisissez la session active pour recalculer les indicateurs et les critères associés.', position: 'bottom' },
      { selector: '.training-kpi-grid', title: 'Indicateurs clés', text: 'Ces cartes synthétisent les performances de la session sélectionnée.', position: 'bottom' },
      { selector: '#trainingDetailPanel', title: 'Pilotage de la session', text: 'Ce panneau centralise le contexte, les statistiques et les actions de gestion.', position: 'left' },
      { selector: '#trainingCriteriaPanel', title: 'Liste des critères', text: 'Cette zone récapitule les critères et les éléments d’évaluation associés à la session.', position: 'left' },
      { selector: '#trainingParticipantsBody', title: 'Participants et notes', text: 'La table permet de suivre les participants, leurs notes et leurs commentaires.', position: 'top' }
    ],
    candidate: [
      { selector: '.candidate-card:first-of-type', title: 'Parcours de candidature', text: 'Commencez par vérifier le contexte de la candidature et les informations de la structure.', position: 'bottom' },
      { selector: '#candidateForm', title: 'Formulaire de candidature', text: 'Complétez les champs requis pour soumettre votre dossier de manière structurée.', position: 'left' },
      { selector: 'input[name="organization"], input[name="organisation"], input[name="country"]', title: 'Organisation et pays', text: 'Le pays est proposé à partir des organisations déjà enregistrées pour rester cohérent avec les données.', position: 'right' },
      { selector: 'button[type="submit"]', title: 'Soumettre le dossier', text: 'Une fois les informations complétées, envoyez votre candidature pour validation.', position: 'top' }
    ]
  };

  function getRuntimeWorkflowSignature() {
    const changes = Array.isArray(window.CritevalTutorial.majorChanges) ? window.CritevalTutorial.majorChanges : workflowHistory;
    const ids = changes.map(change => (change && (change.id || change.name || change.label)) || '').filter(Boolean);
    return [appWorkflowVersion, ...ids].join('|');
  }

  function getTutorialStorageKey(pageKey) {
    const workflowSignature = getRuntimeWorkflowSignature().replace(/[^a-zA-Z0-9_-]+/g, '_');
    return 'criteval_tutorial_' + pageKey + '_' + workflowSignature;
  }

  function getRoutePageKey() {
    const pathname = (window.location.pathname || '').replace(/\/+$/, '');
    if (pathname === '' || pathname === '/' || pathname === '/home') return 'landing';
    if (pathname.indexOf('/login') !== -1) return 'login';
    if (pathname.indexOf('/candidate') !== -1) return 'candidate';
    if (pathname.indexOf('/dashboard') !== -1) return 'dashboard';
    if (document.getElementById('page-dashboard')) return 'dashboard';
    if (document.getElementById('page-login')) return 'login';
    if (document.getElementById('page-landing')) return 'landing';
    if (document.getElementById('candidateForm')) return 'candidate';
    return 'dashboard';
  }

  function getCurrentPageKey() {
    return getRoutePageKey();
  }

  function getPageTutorialSteps(pageKey) {
    const configured = Array.isArray(window.CritevalTutorial.steps) ? window.CritevalTutorial.steps : [];
    if (configured.length) return configured;
    return defaultTutorials[pageKey] || [];
  }

  function ensureLaunchButton() {
    if (document.getElementById('criteval-tutorial-launch')) {
      return document.getElementById('criteval-tutorial-launch');
    }

    const button = document.createElement('button');
    button.id = 'criteval-tutorial-launch';
    button.type = 'button';
    button.className = 'criteval-tutorial-launch';
    button.innerHTML = '<i class="fas fa-compass"></i> Tutoriel';
    button.addEventListener('click', () => startTutorial(getCurrentPageKey()));

    const topbarActions = document.querySelector('.topbar-actions');
    const pageDashboard = document.getElementById('page-dashboard');
    const pageLanding = document.getElementById('page-landing');

    if (topbarActions) {
      topbarActions.appendChild(button);
    } else if (pageDashboard || pageLanding) {
      const wrapper = document.createElement('div');
      wrapper.style.position = 'fixed';
      wrapper.style.bottom = '24px';
      wrapper.style.right = '24px';
      wrapper.style.zIndex = '1200';
      wrapper.appendChild(button);
      document.body.appendChild(wrapper);
    } else {
      document.body.appendChild(button);
    }

    return button;
  }

  function setOverlayPosition(target, highlight, popover) {
    const rect = target.getBoundingClientRect();
    const padding = 18;

    highlight.style.top = (rect.top - padding) + 'px';
    highlight.style.left = (rect.left - padding) + 'px';
    highlight.style.width = (rect.width + padding * 2) + 'px';
    highlight.style.height = (rect.height + padding * 2) + 'px';

    const popoverWidth = Math.min(300, window.innerWidth - 32);
    const sidePadding = 16;
    let left = rect.left + rect.width / 2 - popoverWidth / 2;
    let top = rect.bottom + 18;
    const placement = tutorialState.step && tutorialState.step.position ? tutorialState.step.position : 'bottom';

    if (placement === 'left') {
      left = rect.left - popoverWidth - 16;
      top = rect.top + rect.height / 2 - 70;
    } else if (placement === 'right') {
      left = rect.right + 16;
      top = rect.top + rect.height / 2 - 70;
    } else if (placement === 'top') {
      top = rect.top - 170;
    }

    left = Math.max(sidePadding, Math.min(left, window.innerWidth - popoverWidth - sidePadding));
    top = Math.max(18, Math.min(top, window.innerHeight - 200));

    popover.style.width = popoverWidth + 'px';
    popover.style.left = left + 'px';
    popover.style.top = top + 'px';

    const arrow = popover.querySelector('.criteval-tutorial-arrow');
    if (arrow) {
      arrow.className = 'criteval-tutorial-arrow ' + placement;
      if (placement === 'bottom' || placement === 'top') {
        arrow.style.left = Math.max(18, Math.min(150, rect.left + rect.width / 2 - left)) + 'px';
      } else if (placement === 'left' || placement === 'right') {
        arrow.style.top = '50%';
      }
    }
  }

  function buildPopover(step) {
    const popover = document.createElement('div');
    popover.className = 'criteval-tutorial-popover';
    popover.innerHTML = `
      <div class="criteval-tutorial-arrow ${step.position || 'bottom'}"></div>
      <div class="criteval-tutorial-header">
        <span class="criteval-tutorial-badge">${tutorialState.index + 1}/${tutorialState.steps.length}</span>
        <span class="criteval-tutorial-tag">Tutoriel</span>
      </div>
      <h3>${step.title || 'Étape'}</h3>
      <p>${step.text || ''}</p>
      <div class="criteval-tutorial-actions">
        <button type="button" class="criteval-tutorial-btn secondary" data-tutorial-action="skip">Ignorer</button>
        <button type="button" class="criteval-tutorial-btn" data-tutorial-action="prev">Précédent</button>
        <button type="button" class="criteval-tutorial-btn primary" data-tutorial-action="next">${tutorialState.index === tutorialState.steps.length - 1 ? 'Terminer' : 'Suivant'}</button>
      </div>
    `;

    popover.querySelector('[data-tutorial-action="skip"]').addEventListener('click', () => finishTutorial());
    popover.querySelector('[data-tutorial-action="prev"]').addEventListener('click', () => previousStep());
    popover.querySelector('[data-tutorial-action="next"]').addEventListener('click', () => nextStep());
    return popover;
  }

  function showStep() {
    if (!tutorialState.steps.length) return finishTutorial();
    const step = tutorialState.steps[tutorialState.index];
    if (!step) return finishTutorial();

    tutorialState.step = step;
    const target = document.querySelector(step.selector);
    if (!target) {
      tutorialState.index += 1;
      if (tutorialState.index >= tutorialState.steps.length) return finishTutorial();
      return showStep();
    }

    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    const overlay = tutorialState.overlay || createOverlay();
    tutorialState.overlay = overlay;

    if (!tutorialState.highlight) {
      tutorialState.highlight = document.createElement('div');
      tutorialState.highlight.id = 'criteval-tutorial-highlight';
      overlay.appendChild(tutorialState.highlight);
    }

    if (!tutorialState.popover) {
      tutorialState.popover = document.createElement('div');
      tutorialState.popover.id = 'criteval-tutorial-popover';
      document.body.appendChild(tutorialState.popover);
    }

    tutorialState.popover.replaceChildren();
    tutorialState.popover.appendChild(buildPopover(step));
    setOverlayPosition(target, tutorialState.highlight, tutorialState.popover);
    tutorialState.highlight.classList.add('visible');
    tutorialState.popover.classList.add('visible');

    const focusable = target.querySelector('input, button, select, textarea, a') || target;
    if (focusable && 'focus' in focusable) {
      focusable.focus({ preventScroll: true });
    }
  }

  function createOverlay() {
    const overlay = document.createElement('div');
    overlay.id = 'criteval-tutorial-overlay';
    overlay.setAttribute('aria-hidden', 'true');
    document.body.appendChild(overlay);
    return overlay;
  }

  function nextStep() {
    tutorialState.index += 1;
    if (tutorialState.index >= tutorialState.steps.length) return finishTutorial();
    showStep();
  }

  function previousStep() {
    tutorialState.index = Math.max(0, tutorialState.index - 1);
    showStep();
  }

  function finishTutorial() {
    const pageKey = getCurrentPageKey();
    if (tutorialState.overlay) {
      tutorialState.overlay.remove();
      tutorialState.overlay = null;
    }
    if (tutorialState.popover) {
      tutorialState.popover.remove();
      tutorialState.popover = null;
    }
    tutorialState.visible = false;
    tutorialState.index = 0;
    tutorialState.step = null;
    try { localStorage.setItem(getTutorialStorageKey(pageKey), 'done'); } catch (error) {}
  }

  function startTutorial(pageKey = getCurrentPageKey()) {
    const steps = getPageTutorialSteps(pageKey);
    if (!steps || !steps.length) return;

    tutorialState.page = pageKey;
    tutorialState.steps = steps;
    tutorialState.index = 0;
    tutorialState.visible = true;
    if (tutorialState.overlay) tutorialState.overlay.remove();
    if (tutorialState.popover) tutorialState.popover.remove();
    tutorialState.overlay = createOverlay();
    tutorialState.highlight = document.createElement('div');
    tutorialState.highlight.id = 'criteval-tutorial-highlight';
    tutorialState.overlay.appendChild(tutorialState.highlight);
    tutorialState.popover = document.createElement('div');
    tutorialState.popover.id = 'criteval-tutorial-popover';
    document.body.appendChild(tutorialState.popover);
    showStep();
  }

  function initTutorialSystem() {
    const launchButton = ensureLaunchButton();
    if (launchButton) {
      launchButton.title = 'Lancer le tutoriel';
      launchButton.setAttribute('aria-label', 'Lancer le tutoriel');
    }

    const pageKey = getCurrentPageKey();
    const tutorialKey = getTutorialStorageKey(pageKey);
    const hasSeenTutorial = (() => {
      try { return localStorage.getItem(tutorialKey) === 'done'; } catch (error) { return false; }
    })();

    if (!hasSeenTutorial && ['dashboard', 'landing', 'login', 'candidate'].includes(pageKey)) {
      setTimeout(() => startTutorial(pageKey), 900);
    }
  }

  window.CritevalTutorial.start = startTutorial;
  window.CritevalTutorial.finish = finishTutorial;
  window.CritevalTutorial.reset = function() {
    const pageKey = getCurrentPageKey();
    try { localStorage.removeItem(getTutorialStorageKey(pageKey)); } catch (error) {}
    startTutorial(pageKey);
  };
  window.CritevalTutorial.registerMajorChange = function(change) {
    const changes = Array.isArray(window.CritevalTutorial.majorChanges) ? window.CritevalTutorial.majorChanges : workflowHistory;
    const item = change && typeof change === 'object' ? change : { id: String(change || Date.now()), label: 'Changement majeur' };
    if (!changes.some(existing => (existing.id || existing.label) === (item.id || item.label))) {
      changes.push(item);
    }
    window.CritevalTutorial.majorChanges = changes;
    try {
      localStorage.setItem('criteval_tutorial_workflow_history', JSON.stringify(changes));
    } catch (error) {}
  };
  window.CritevalTutorial.getWorkflowSignature = getRuntimeWorkflowSignature;
  window.CritevalTutorial.ready = true;

  try {
    const storedWorkflowHistory = localStorage.getItem('criteval_tutorial_workflow_history');
    if (storedWorkflowHistory) {
      const parsed = JSON.parse(storedWorkflowHistory);
      if (Array.isArray(parsed) && parsed.length) {
        window.CritevalTutorial.majorChanges = parsed;
      }
    }
  } catch (error) {}

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTutorialSystem, { once: true });
  } else {
    initTutorialSystem();
  }
})();

// ══════════════════════════════════════
// MODALS
// ══════════════════════════════════════
function confirmDelete() { $('#modalDelete').addClass('open'); }

$('.modal-overlay').on('click', function(e) {
  if (e.target === this) $(this).removeClass('open');
});

$('.modal-close').on('click', function() {
  $(this).closest('.modal-overlay').removeClass('open');
});

// ══════════════════════════════════════
// SIDEBAR TOGGLE
// ══════════════════════════════════════
$(document).ready(function() {
  $('#sidebarToggle').on('click', function() {
    $('#sidebar').toggleClass('collapsed');
    $('#topbar').toggleClass('expanded');
    $('#mainContent').toggleClass('expanded');
  });

  // Chart period buttons
  $(document).on('click', '.chart-btn', function() {
    $(this).siblings().removeClass('active');
    $(this).addClass('active');
  });

  // Criteria drag & drop
  if ($('#criteriaList').length && !$('#criteriaList').hasClass('ui-sortable')) {
    $('#criteriaList').sortable({ handle: '.drag-handle', axis: 'y', animation: 150 });
  }
  if ($('#kpiSortable').length && !$('#kpiSortable').hasClass('ui-sortable')) {
    $('#kpiSortable').sortable({ handle: '.widget-handle', animation: 150, tolerance: 'pointer' });
  }

  // Canvas element selection
  $(document).on('click', '.canvas-element', function() {
    $('.canvas-element').removeClass('selected');
    $(this).addClass('selected');
  });

  // Navbar scroll effect
  $(window).on('scroll', function() {
    if ($(this).scrollTop() > 50) $('#pubNav').addClass('scrolled');
    else $('#pubNav').removeClass('scrolled');
  });

  // Landing page stats counter observer
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) animateStats(); });
  }, { threshold: 0.3 });
  const statsSection = document.getElementById('stats');
  if (statsSection) observer.observe(statsSection);

  // Init pages
  if (document.getElementById('page-landing')) {
    setTimeout(animateHeroStats, 500);
    setTimeout(initAfricaMap, 200);
  }
  if (document.getElementById('page-dashboard')) {
    setTimeout(() => { initCharts(); animateKPIs(); initCalendar(); }, 200);
  }
});

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
      { name: 'Ghana', latlng: [7.946, -1.023], count: 11 }
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
// LOGIN FUNCTIONALITY
// ══════════════════════════════════════
function loginToDash() {
  navigateTo('dashboard');
}

}
