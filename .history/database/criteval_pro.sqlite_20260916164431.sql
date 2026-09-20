-- SQLite schema for the MVP. Keep database/criteval_pro.sql as the future MySQL schema.
PRAGMA foreign_keys = ON;

CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL UNIQUE, name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE, password TEXT NOT NULL, role TEXT NOT NULL DEFAULT 'admin'
    CHECK (role IN ('superadmin', 'admin', 'user', 'visitor')), is_active INTEGER NOT NULL DEFAULT 1,
  avatar TEXT, permissions TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE projects (
  id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, organization TEXT, intervention_zone TEXT,
  description TEXT, objectives TEXT, domains TEXT, target_audiences TEXT, legal_status TEXT NOT NULL DEFAULT 'non_legal',
  country_code TEXT NOT NULL DEFAULT '', project_count INTEGER, contact_name TEXT, contact_phone TEXT,
  contact_email TEXT, website TEXT, budget_requested REAL, duration_months INTEGER, logo_path TEXT,
  status TEXT NOT NULL DEFAULT 'draft'
    CHECK (status IN ('draft', 'active', 'closed', 'archived')), created_by INTEGER,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
);
CREATE TABLE project_gallery (
  id INTEGER PRIMARY KEY AUTOINCREMENT, project_id INTEGER NOT NULL, file_path TEXT, caption TEXT,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
CREATE TABLE criteria (
  id INTEGER PRIMARY KEY AUTOINCREMENT, project_id INTEGER NOT NULL, label TEXT NOT NULL, description TEXT,
  weight REAL NOT NULL DEFAULT 1.0, max_score REAL NOT NULL DEFAULT 20.0, is_required INTEGER NOT NULL DEFAULT 1,
  order_index INTEGER NOT NULL DEFAULT 0, source_template_id INTEGER,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
CREATE TABLE criteria_templates (
  id INTEGER PRIMARY KEY AUTOINCREMENT, label TEXT NOT NULL, description TEXT,
  weight REAL NOT NULL DEFAULT 1.0, max_score REAL NOT NULL DEFAULT 20.0,
  is_required INTEGER NOT NULL DEFAULT 1, order_index INTEGER NOT NULL DEFAULT 0,
  is_active INTEGER NOT NULL DEFAULT 1, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE project_criteria (
  project_id INTEGER NOT NULL, criteria_id INTEGER NOT NULL, order_index INTEGER NOT NULL DEFAULT 0,
  PRIMARY KEY (project_id, criteria_id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (criteria_id) REFERENCES criteria(id) ON DELETE CASCADE
);
CREATE TABLE forms (
  id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, description TEXT, project_id INTEGER,
  layout_json TEXT, status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')),
  created_by INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id), FOREIGN KEY (created_by) REFERENCES users(id)
);
CREATE TABLE form_schedules (
  id INTEGER PRIMARY KEY AUTOINCREMENT, form_id INTEGER NOT NULL, project_id INTEGER,
  access_type TEXT NOT NULL DEFAULT 'public_link' CHECK (access_type IN ('public_link', 'email_list', 'admin_only')),
  start_datetime TEXT, end_datetime TEXT, allowed_emails TEXT, allowed_countries TEXT, is_active INTEGER NOT NULL DEFAULT 1,
  FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
);
CREATE TABLE submissions (
  id INTEGER PRIMARY KEY AUTOINCREMENT, form_id INTEGER NOT NULL, candidate_email TEXT NOT NULL,
  candidate_name TEXT, country_code TEXT, data_json TEXT, ip_address TEXT,
  submitted_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'under_review', 'evaluated', 'published')),
  FOREIGN KEY (form_id) REFERENCES forms(id)
);
CREATE TABLE otp_tokens (
  id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, token TEXT NOT NULL, form_id INTEGER,
  expires_at TEXT NOT NULL, is_used INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE evaluation_levels (
  id INTEGER PRIMARY KEY AUTOINCREMENT, score_min REAL, score_max REAL, title TEXT NOT NULL,
  description TEXT, remarks TEXT, color_hex TEXT NOT NULL DEFAULT '#2EAF7D'
);
CREATE TABLE evaluations (
  id INTEGER PRIMARY KEY AUTOINCREMENT, submission_id INTEGER NOT NULL, criteria_id INTEGER NOT NULL,
  score REAL NOT NULL, comment TEXT, evaluated_by INTEGER, evaluated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
  FOREIGN KEY (criteria_id) REFERENCES criteria(id), FOREIGN KEY (evaluated_by) REFERENCES users(id)
);
CREATE TABLE results (
  id INTEGER PRIMARY KEY AUTOINCREMENT, submission_id INTEGER NOT NULL UNIQUE, project_id INTEGER NOT NULL,
  total_score REAL, weighted_score REAL, rank_position INTEGER, level_id INTEGER, is_published INTEGER NOT NULL DEFAULT 0,
  published_at TEXT, FOREIGN KEY (submission_id) REFERENCES submissions(id),
  FOREIGN KEY (project_id) REFERENCES projects(id), FOREIGN KEY (level_id) REFERENCES evaluation_levels(id)
);
CREATE TABLE module_settings (
  id INTEGER PRIMARY KEY AUTOINCREMENT, module_key TEXT NOT NULL UNIQUE, is_enabled INTEGER NOT NULL DEFAULT 1,
  label TEXT, icon TEXT
);

CREATE TABLE organization_evidence (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  submission_id INTEGER NOT NULL,
  criteria_id INTEGER NOT NULL,
  file_path TEXT NOT NULL,
  original_name TEXT NOT NULL,
  mime_type TEXT,
  file_size INTEGER NOT NULL DEFAULT 0,
  uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
  FOREIGN KEY (criteria_id) REFERENCES criteria(id) ON DELETE CASCADE
);

CREATE TABLE training_sessions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  objective TEXT NOT NULL,
  session_date DATE NOT NULL,
  project_id INTEGER,
  capacity INTEGER NOT NULL DEFAULT 50,
  format TEXT NOT NULL DEFAULT 'hybride',
  evaluation_weight REAL NOT NULL DEFAULT 0,
  public_slug TEXT NOT NULL UNIQUE,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_by INTEGER,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id)
);
CREATE TABLE training_attendance (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  session_id INTEGER NOT NULL,
  submission_id INTEGER NOT NULL,
  attendance_status TEXT NOT NULL DEFAULT 'registered',
  grade REAL,
  comment TEXT,
  included_in_evaluation INTEGER NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(session_id, submission_id),
  FOREIGN KEY (session_id) REFERENCES training_sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
);
CREATE INDEX idx_projects_status ON projects(status);
CREATE INDEX idx_submissions_status_date ON submissions(status, submitted_at);
CREATE INDEX idx_submissions_form ON submissions(form_id);

INSERT INTO users (username, name, email, password, role, is_active) VALUES
  ('admin', 'Super Administrateur', 'admin@criteval.pro', '$2y$12$6K5WXKXlx2o4drPLAVAZOOucu7zSpO/9.hqn55Rb7axu.Pnrh8RB2', 'superadmin', 1);
INSERT INTO module_settings (module_key, is_enabled, label, icon) VALUES
  ('apercu', 1, 'Aperçu', 'fa-home'), ('projets', 1, 'Projets', 'fa-folder-open'),
  ('criteres', 1, 'Critères', 'fa-check-square'), ('formulaires', 1, 'Formulaires', 'fa-file-alt'),
  ('evaluations', 1, 'Évaluations', 'fa-star');
