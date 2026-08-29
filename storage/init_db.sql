-- Criteval_pro database schema
-- Source of truth: docs/PROMPT_CRITEVAL_PRO.md, "SCHEMA BASE DE DONNEES"

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS criteval_pro
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE criteval_pro;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS module_settings;
DROP TABLE IF EXISTS results;
DROP TABLE IF EXISTS evaluations;
DROP TABLE IF EXISTS evaluation_levels;
DROP TABLE IF EXISTS otp_tokens;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS form_schedules;
DROP TABLE IF EXISTS forms;
DROP TABLE IF EXISTS criteria;
DROP TABLE IF EXISTS project_gallery;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Utilisateurs (admins + super-admins)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(191) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('superadmin', 'admin') DEFAULT 'admin',
  is_active TINYINT(1) DEFAULT 1,
  avatar VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projets
CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  organization VARCHAR(255),
  intervention_zone VARCHAR(255),
  description TEXT,
  objectives TEXT,
  status ENUM('draft','active','closed','archived') DEFAULT 'draft',
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Galerie projets
CREATE TABLE project_gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  file_path VARCHAR(255),
  caption VARCHAR(255),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criteres d'evaluation
CREATE TABLE criteria (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  label VARCHAR(255) NOT NULL,
  description TEXT,
  weight DECIMAL(5,2) DEFAULT 1.00,
  max_score DECIMAL(5,2) DEFAULT 20.00,
  is_required TINYINT(1) DEFAULT 1,
  order_index INT DEFAULT 0,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Formulaires
CREATE TABLE forms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  project_id INT,
  layout_json LONGTEXT,
  status ENUM('draft','published','archived') DEFAULT 'draft',
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Planification formulaires
CREATE TABLE form_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  form_id INT NOT NULL,
  project_id INT,
  access_type ENUM('public_link','email_list','admin_only') DEFAULT 'public_link',
  start_datetime DATETIME,
  end_datetime DATETIME,
  allowed_emails TEXT,
  allowed_countries TEXT,
  is_active TINYINT(1) DEFAULT 1,
  FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Soumissions candidats
CREATE TABLE submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  form_id INT NOT NULL,
  candidate_email VARCHAR(191) NOT NULL,
  candidate_name VARCHAR(150),
  country_code VARCHAR(5),
  data_json LONGTEXT,
  ip_address VARCHAR(45),
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending','under_review','evaluated','published') DEFAULT 'pending',
  FOREIGN KEY (form_id) REFERENCES forms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OTP candidats
CREATE TABLE otp_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(191) NOT NULL,
  token VARCHAR(255) NOT NULL,
  form_id INT,
  expires_at DATETIME NOT NULL,
  is_used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Niveaux d'evaluation
CREATE TABLE evaluation_levels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  score_min DECIMAL(5,2),
  score_max DECIMAL(5,2),
  title VARCHAR(100) NOT NULL,
  description TEXT,
  remarks TEXT,
  color_hex VARCHAR(7) DEFAULT '#2EAF7D'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Evaluations
CREATE TABLE evaluations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  submission_id INT NOT NULL,
  criteria_id INT NOT NULL,
  score DECIMAL(5,2) NOT NULL,
  comment TEXT,
  evaluated_by INT,
  evaluated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
  FOREIGN KEY (criteria_id) REFERENCES criteria(id),
  FOREIGN KEY (evaluated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resultats finaux
CREATE TABLE results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  submission_id INT UNIQUE NOT NULL,
  project_id INT NOT NULL,
  total_score DECIMAL(6,2),
  weighted_score DECIMAL(6,2),
  rank_position INT,
  level_id INT,
  is_published TINYINT(1) DEFAULT 0,
  published_at DATETIME,
  FOREIGN KEY (submission_id) REFERENCES submissions(id),
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (level_id) REFERENCES evaluation_levels(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modules activables/desactivables
CREATE TABLE module_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  module_key VARCHAR(50) UNIQUE NOT NULL,
  is_enabled TINYINT(1) DEFAULT 1,
  label VARCHAR(100),
  icon VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, name, email, password, role, is_active) VALUES
('admin', 'Super Administrateur', 'admin@criteval.pro', '$2y$12$6K5WXKXlx2o4drPLAVAZOOucu7zSpO/9.hqn55Rb7axu.Pnrh8RB2', 'superadmin', 1);

INSERT INTO projects (id, title, organization, intervention_zone, description, objectives, status, created_by, created_at, updated_at) VALUES
(1, 'Résilience climatique pour femmes rurales', 'Réseau des Femmes Leaders du Mali', 'Mali — Région de Sikasso', 'Programme de renforcement des capacités de 240 femmes rurales à adapter leurs exploitations aux changements climatiques.', 'Sécuriser les revenus, améliorer la résilience des ménages et promouvoir des pratiques agroécologiques durables.', 'active', 1, NOW(), NOW()),
(2, 'Accès à l’eau potable et gouvernance locale', 'Plateforme des OSC pour l’eau et l’environnement — Burkina Faso', 'Burkina Faso — Centre-Nord', 'Projet de construction de points d’eau sécurisés et de renforcement des comités de gestion communautaires.', 'Améliorer l’accès à l’eau potable, renforcer la gouvernance locale et réduire les conflits autour des ressources.', 'active', 1, NOW(), NOW()),
(3, 'Protection des jeunes entrepreneurs informels', 'Réseau des Jeunes Entrepreneures du Sénégal', 'Sénégal — Dakar et Thiès', 'Initiative de formalisation et d’accompagnement de jeunes entrepreneurs informels dans les zones urbaines et périurbaines.', 'Renforcer la formalisation économique, améliorer l’accès aux services et accroître la résilience des micro-entreprises.', 'active', 1, NOW(), NOW()),
(4, 'Éducation numérique pour filles rurales', 'Association des Femmes de Côte d’Ivoire pour l’innovation', 'Côte d’Ivoire — Tonkpi', 'Programme d’accès à l’éducation numérique et d’accompagnement des filles en milieu rural.', 'Réduire l’écart d’accès aux outils numériques et stimuler la persévérance scolaire des filles.', 'active', 1, NOW(), NOW()),
(5, 'Sécurité alimentaire et semences locales', 'Réseau des agroécologues du Rwanda', 'Rwanda — Province du Sud', 'Projet de multiplication de semences locales et de formation aux pratiques agroécologiques.', 'Renforcer la souveraineté alimentaire, préserver la biodiversité et stabiliser les rendements.', 'draft', 1, NOW(), NOW()),
(6, 'Prévention des mariages précoces et violences', 'Coalition Togo des OSC pour les droits des filles', 'Togo — Région des Savanes', 'Programme de prévention des mariages précoces, de sensibilisation des communautés et d’appui aux structures de protection.', 'Protéger les filles, réduire les violences et renforcer les mécanismes d’appui communautaire.', 'active', 1, NOW(), NOW());

INSERT INTO project_gallery (id, project_id, file_path, caption) VALUES
(1, 1, 'uploads/gallery/project-1-1.jpg', 'Atelier de formation sur l’agroécologie'),
(2, 1, 'uploads/gallery/project-1-2.jpg', 'Femmes rurales établissant des parcelles résilientes'),
(3, 2, 'uploads/gallery/project-2-1.jpg', 'Réception d’eau potable dans village communautaire'),
(4, 3, 'uploads/gallery/project-3-1.jpg', 'Jeunes entrepreneurs participant à un incubateur urbain'),
(5, 4, 'uploads/gallery/project-4-1.jpg', 'Session de codage pour filles rurales'),
(6, 6, 'uploads/gallery/project-6-1.jpg', 'Atelier de prévention et plaidoyer');

INSERT INTO forms (id, title, description, project_id, layout_json, status, created_by, created_at) VALUES
(1, 'Candidature — Résilience climatique', 'Formulaire de candidature pour le programme de résilience climatique dédié aux femmes rurales.', 1, '{"fields":[{"type":"text","label":"Nom de l’organisation"},{"type":"textarea","label":"Résumé du projet"},{"type":"number","label":"Budget demandé (EUR)"}]}', 'published', 1, NOW()),
(2, 'Candidature — Eau et gouvernance', 'Formulaire de candidature pour l’accès à l’eau potable et la gouvernance locale.', 2, '{"fields":[{"type":"text","label":"Nom du comité"},{"type":"textarea","label":"Description des actions"},{"type":"select","label":"Type de gouvernance","options":["Comité local","Coopérative","ONG"]}]}', 'published', 1, NOW()),
(3, 'Candidature — Jeunes entrepreneurs', 'Formulaire de candidature pour les jeunes entrepreneurs informels du Sénégal.', 3, '{"fields":[{"type":"text","label":"Nom du porteur"},{"type":"text","label":"Secteur d’activité"},{"type":"textarea","label":"Impact prévisionnel"}]}', 'published', 1, NOW()),
(4, 'Candidature — Éducation numérique', 'Formulaire de candidature pour l’éducation numérique des filles rurales.', 4, '{"fields":[{"type":"text","label":"Ecole / ONG"},{"type":"text","label":"Nombre de filles cibles"},{"type":"textarea","label":"Plan de durabilité"}]}', 'published', 1, NOW()),
(5, 'Candidature — Semences locales', 'Formulaire de candidature pour le renforcement de la sécurité alimentaire.', 5, '{"fields":[{"type":"text","label":"Coopérative agricole"},{"type":"textarea","label":"Variétés de semences"},{"type":"number","label":"Mètres carrés de production"}]}', 'draft', 1, NOW()),
(6, 'Candidature — Droits des filles', 'Formulaire de candidature pour la prévention des mariages précoces et violences.', 6, '{"fields":[{"type":"text","label":"Nom de l’organisation"},{"type":"text","label":"Zone d’intervention"},{"type":"textarea","label":"Approche de plaidoyer"}]}', 'published', 1, NOW());

INSERT INTO form_schedules (id, form_id, project_id, access_type, start_datetime, end_datetime, allowed_emails, allowed_countries, is_active) VALUES
(1, 1, 1, 'public_link', DATE_ADD(NOW(), INTERVAL -14 DAY), DATE_ADD(NOW(), INTERVAL 16 DAY), NULL, 'ML,BF,SN,CI,TG', 1),
(2, 2, 2, 'public_link', DATE_ADD(NOW(), INTERVAL -20 DAY), DATE_ADD(NOW(), INTERVAL 10 DAY), NULL, 'BF,ML,CI', 1),
(3, 3, 3, 'public_link', DATE_ADD(NOW(), INTERVAL -10 DAY), DATE_ADD(NOW(), INTERVAL 20 DAY), NULL, 'SN,CI,TG', 1),
(4, 4, 4, 'email_list', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL 25 DAY), 'amina.kone@example.ml,khadija.ndiaye@example.sn', 'CI,ML', 1),
(5, 6, 6, 'admin_only', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL 30 DAY), NULL, 'TG', 1);

INSERT INTO criteria (id, project_id, label, description, weight, max_score, is_required, order_index) VALUES
(1, 1, 'Pertinence du projet', 'Alignement avec les besoins des femmes rurales et la résilience climatique.', 2.00, 20.00, 1, 1),
(2, 1, 'Impact communautaire', 'Portée attendue sur les ménages et les territoires.', 1.50, 20.00, 1, 2),
(3, 1, 'Viabilité du modèle', 'Capacité de pérennisation après la phase de financement.', 1.00, 20.00, 1, 3),
(4, 2, 'Pertinence du projet', 'Adaptation aux besoins en eau et gestion locale des ressources.', 2.00, 20.00, 1, 1),
(5, 2, 'Capacité institutionnelle', 'Capacité des organisations à piloter le projet localement.', 1.50, 20.00, 1, 2),
(6, 3, 'Pertinence du projet', 'Adéquation du projet aux besoins économiques des jeunes.', 2.00, 20.00, 1, 1),
(7, 3, 'Innovation et croissance', 'Potentiel de développement des activités et du chiffre d’affaires.', 1.50, 20.00, 1, 2),
(8, 4, 'Pertinence éducative', 'Adéquation avec les besoins d’accès à l’éducation numérique.', 2.00, 20.00, 1, 1),
(9, 4, 'Impact filles', 'Effets attendus sur l’inclusion et la persévérance scolaire.', 1.50, 20.00, 1, 2),
(10, 5, 'Pertinence agricole', 'Adéquation aux besoins de souveraineté alimentaire.', 2.00, 20.00, 1, 1),
(11, 5, 'Durabilité technique', 'Capacité de diffusion et de maintenance des pratiques.', 1.00, 20.00, 1, 2),
(12, 6, 'Pertinence sociale', 'Réponse aux besoins de protection des filles et des femmes.', 2.00, 20.00, 1, 1),
(13, 6, 'Capacité de plaidoyer', 'Capacité à mobiliser les acteurs et à influencer les politiques.', 1.50, 20.00, 1, 2);

INSERT INTO submissions (id, form_id, candidate_email, candidate_name, country_code, data_json, ip_address, submitted_at, status) VALUES
(1, 1, 'amina.kone@example.ml', 'Amina Koné', 'ML', '{"organization":"Réseau des Femmes Leaders du Mali","project":"Résilience climatique pour femmes rurales","budget":"82 000 EUR","summary":"Renforcement des capacités et adaptation climatique pour les groupements féminins."}', '192.168.10.10', DATE_ADD(NOW(), INTERVAL -12 DAY), 'under_review'),
(2, 2, 'souleymane.diallo@example.bf', 'Souleymane Diallo', 'BF', '{"organization":"Plateforme des OSC pour l’eau et l’environnement","project":"Accès à l’eau potable et gouvernance locale","budget":"120 000 EUR","summary":"Construction de points d’eau et formation de comités de gestion."}', '192.168.10.11', DATE_ADD(NOW(), INTERVAL -8 DAY), 'pending'),
(3, 3, 'khadija.ndiaye@example.sn', 'Khadija Ndiaye', 'SN', '{"organization":"Réseau des Jeunes Entrepreneures du Sénégal","project":"Protection des jeunes entrepreneurs informels","budget":"55 000 EUR","summary":"Programme de formalisation et accompagnement des micro-entreprises."}', '192.168.10.12', DATE_ADD(NOW(), INTERVAL -7 DAY), 'evaluated'),
(4, 4, 'nadia.kouassi@example.ci', 'Nadia Kouassi', 'CI', '{"organization":"Association des Femmes de Côte d’Ivoire pour l’innovation","project":"Éducation numérique pour filles rurales","budget":"70 000 EUR","summary":"Installation de salles numériques et formation des enseignantes."}', '192.168.10.13', DATE_ADD(NOW(), INTERVAL -5 DAY), 'pending'),
(5, 6, 'fadime.yovo@example.tg', 'Fadime Yovo', 'TG', '{"organization":"Coalition Togo des OSC","project":"Prévention des mariages précoces et violences","budget":"65 000 EUR","summary":"Campagnes communautaires et appui aux centres de protection."}', '192.168.10.14', DATE_ADD(NOW(), INTERVAL -3 DAY), 'pending'),
(6, 1, 'koumba.camara@example.ml', 'Koumba Camara', 'ML', '{"organization":"Association des Femmes de Bougouni","project":"Résilience climatique pour femmes rurales","budget":"91 000 EUR","summary":"Appui technique aux groupements agricoles de la zone de Sikasso."}', '192.168.10.15', DATE_ADD(NOW(), INTERVAL -2 DAY), 'under_review'),
(7, 3, 'ibrahima.diop@example.sn', 'Ibrahima Diop', 'SN', '{"organization":"Start-up Solidaire Dakar","project":"Protection des jeunes entrepreneurs informels","budget":"45 000 EUR","summary":"Espaces de co-working et accompagnement business pour jeunes femmes."}', '192.168.10.16', DATE_ADD(NOW(), INTERVAL -1 DAY), 'pending'),
(8, 2, 'ameenat.traore@example.bf', 'Ameenat Traoré', 'BF', '{"organization":"Club Eau et Vie","project":"Accès à l’eau potable et gouvernance locale","budget":"98 000 EUR","summary":"Réhabilitation de puits et renforcement des comités d’eau villageois."}', '192.168.10.17', NOW(), 'pending');

INSERT INTO otp_tokens (id, email, token, form_id, expires_at, is_used, created_at) VALUES
(1, 'amina.kone@example.ml', 'A1M2N3', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 0, NOW()),
(2, 'souleymane.diallo@example.bf', 'S4U5L6', 2, DATE_ADD(NOW(), INTERVAL 7 DAY), 0, NOW()),
(3, 'khadija.ndiaye@example.sn', 'K7H8J9', 3, DATE_ADD(NOW(), INTERVAL 7 DAY), 0, NOW());

INSERT INTO evaluations (id, submission_id, criteria_id, score, comment, evaluated_by, evaluated_at) VALUES
(1, 3, 6, 16.5, 'Bonne pertinence du projet et fort potentiel de croissance.', 1, DATE_ADD(NOW(), INTERVAL -4 DAY)),
(2, 3, 7, 15.0, 'Approche innovante et modèle économique crédible.', 1, DATE_ADD(NOW(), INTERVAL -4 DAY)),
(3, 1, 1, 14.0, 'Très bon alignement sur les enjeux climatiques.', 1, DATE_ADD(NOW(), INTERVAL -3 DAY)),
(4, 1, 2, 13.5, 'Impact communautaire bien ciblé.', 1, DATE_ADD(NOW(), INTERVAL -3 DAY)),
(5, 1, 3, 12.0, 'Plan de pérennisation pertinent mais devra être renforcé.', 1, DATE_ADD(NOW(), INTERVAL -3 DAY));

INSERT INTO results (id, submission_id, project_id, total_score, weighted_score, rank_position, level_id, is_published, published_at) VALUES
(1, 3, 3, 31.5, 15.8, 1, 4, 1, DATE_ADD(NOW(), INTERVAL -3 DAY)),
(2, 1, 1, 39.5, 14.8, 2, 3, 0, NULL);

INSERT INTO evaluation_levels (score_min, score_max, title, description, remarks, color_hex) VALUES
(0.00, 9.99, 'Insuffisant', 'Score sous le seuil attendu.', 'Dossier a renforcer avant selection.', '#E74C3C'),
(10.00, 13.99, 'Moyen', 'Score acceptable avec reserves.', 'Dossier admissible sous conditions.', '#F5A623'),
(14.00, 16.99, 'Bon', 'Score solide et conforme aux attentes.', 'Dossier recommande.', '#3498DB'),
(17.00, 20.00, 'Excellent', 'Score tres eleve.', 'Dossier prioritaire.', '#2EAF7D');

INSERT INTO module_settings (module_key, is_enabled, label, icon) VALUES
('apercu', 1, 'Aperçu', 'fa-home'),
('projets', 1, 'Projets', 'fa-folder-open'),
('criteres', 1, 'Critères', 'fa-check-square'),
('formulaires', 1, 'Formulaires', 'fa-file-alt'),
('formation', 1, 'Formation', 'fa-chalkboard-teacher'),
('evaluations', 1, 'Évaluations', 'fa-star'),
('classements', 1, 'Classements', 'fa-trophy'),
('calendrier', 1, 'Planning', 'fa-calendar-alt'),
('parametres', 1, 'Paramètres', 'fa-cog');
