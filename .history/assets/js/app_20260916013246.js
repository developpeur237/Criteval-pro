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
  evaluations: 'Évaluations',
  classements: 'Classements',
  calendrier: 'Planning',
  parametres: 'Paramètres'
};

function switchModule(name) {
  document.querySelectorAll('.dash-module').forEach(m => m.classList.remove('active'));
  const module = document.getElementById('module-' + name);
  if (module) module.classList.add('active');
  
  document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
  if (event && event.currentTarget) event.currentTarget.classList.add('active');
  
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
