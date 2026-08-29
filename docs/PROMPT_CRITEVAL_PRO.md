# ?? PROMPT MA�TRE - CRITEVAL_PRO
### Logiciel Web Professionnel d'�valuation de Financement de Projets
---

## ?? CONTEXTE G�N�RAL

Tu es un d�veloppeur web full-stack senior expert en PHP natif, HTML5, CSS3, JavaScript vanille, jQuery, jQuery UI, Tailwind CSS, et int�grations tierces (PDF, cartographie, emailing, formulaires dynamiques WYSIWYG). Tu dois construire de A � Z une application web professionnelle nomm�e **Criteval_pro**, un logiciel SaaS d'�valuation de demandes de financement de projets, bas� sur un syst�me de crit�res param�trables et de notation structur�e.

L'application sera d'abord d�ploy�e et test�e en **local (XAMPP / WAMP / Laragon)** puis h�berg�e sur un **serveur cPanel Hostinger** en production avec HTTPS activ�. Aucun framework PHP (pas de Laravel, Symfony, etc.) - uniquement PHP natif structur�. Aucun TypeScript, Python, React, Dart, Vue ou framework JS lourd.

---

## ??? STACK TECHNIQUE OBLIGATOIRE

| Couche | Technologie |
|---|---|
| Backend | PHP 8.x natif (POO, architecture MVC maison) |
| Base de donn�es | MySQL / MariaDB (PDO, requ�tes pr�par�es) |
| Frontend | HTML5, CSS3, Tailwind CSS (via CDN ou build) |
| Interactivit� | jQuery 3.x + jQuery UI (drag, resize, sort, datepicker.) |
| Animations | Animate.css + AOS (Animate On Scroll) ou GSAP l�ger |
| G�n�ration PDF | TCPDF ou FPDF (PHP natif - Best Simple PDF) |
| Cartographie | Leaflet.js + API African Country Library (REST Countries ou equivalent Afrique) |
| Emailing | PHPMailer (SMTP Hostinger + templates HTML) |
| �diteur WYSIWYG | TinyMCE ou Quill.js (int�gration jQuery) |
| Drag & Drop Formulaire | jQuery UI Draggable/Droppable + syst�me de grille canvas personnalis� |
| Graphiques | Chart.js (l�ger, compatible jQuery) |
| Ic�nes | Font Awesome 6 Free |
| Calendrier/Planning | FullCalendar.js (version jQuery) |
| Auth OTP / 2FA | PHP custom OTP par email via PHPMailer |
| Sessions & S�curit� | PHP Sessions, CSRF tokens, XSS filtering, bcrypt passwords |

---

## ?? IDENTIT� VISUELLE & DESIGN

### Palette de couleurs
```
--primary       : #1A3C5E   (Bleu marine professionnel - confiance, s�rieux)
--secondary     : #2EAF7D   (Vert �meraude - croissance, Afrique, financement)
--accent        : #F5A623   (Or ambr� - valeur, excellence, classement)
--danger        : #E74C3C   (Rouge - alertes, suppression)
--neutral-dark  : #1E1E2E   (Fond sombre dashboard)
--neutral-light : #F4F6FA   (Fond clair pages publiques)
--text-main     : #2C3E50
--text-muted    : #7F8C8D
--white         : #FFFFFF
--border        : #DDE1E7
```

### Typographie
- **Titres / Display** : `Poppins` (Bold 700 / SemiBold 600) - Google Fonts
- **Corps / Paragraphes** : `Inter` (Regular 400 / Medium 500) - Google Fonts
- **Donn�es / Tableaux** : `JetBrains Mono` ou `Roboto Mono` (pour notes, scores)

### Principes UI
- Design **card-based**, ombres douces (`box-shadow` �tag�es), coins arrondis (`border-radius: 12px`)
- Mode sombre natif pour le **dashboard admin** (fond `#1E1E2E`)
- Mode clair pour la **section publique** et les **formulaires candidats**
- Animations d'entr�e de pages via **AOS** (fade-up, fade-right) - utilis�es avec parcimonie
- Micro-interactions jQuery sur les boutons, les toggles et les cartes
- Totalement **responsive** (mobile-first via Tailwind breakpoints)
- L'**�l�ment signature** : un syst�me de score visuel circulaire anim� (SVG + jQuery), utilis� dans les r�sultats et le dashboard, imm�diatement reconnaissable et unique � Criteval_pro.

---

## ?? ARCHITECTURE DU PROJET

```
criteval_pro/
?
??? index.php                    # Point d'entr�e - router maison
??? .htaccess                    # R��criture d'URL + s�curit�
??? config/
?   ??? database.php             # Connexion PDO MySQL
?   ??? config.php               # Constantes globales (APP_NAME, BASE_URL, etc.)
?   ??? mailer.php               # Config PHPMailer SMTP
?   ??? auth.php                 # Gestion sessions / r�les
?
??? controllers/
?   ??? HomeController.php
?   ??? AuthController.php
?   ??? AdminController.php
?   ??? ProjectController.php
?   ??? CriteriaController.php
?   ??? FormController.php
?   ??? EvaluationController.php
?   ??? CandidateController.php
?   ??? PdfController.php
?
??? models/
?   ??? User.php
?   ??? Project.php
?   ??? Criteria.php
?   ??? Form.php
?   ??? FormField.php
?   ??? Submission.php
?   ??? Evaluation.php
?   ??? Ranking.php
?
??? views/
?   ??? layouts/
?   ?   ??? public_layout.php    # Layout section publique
?   ?   ??? admin_layout.php     # Layout dashboard admin
?   ?   ??? form_layout.php      # Layout formulaire candidat
?   ?
?   ??? public/
?   ?   ??? home.php             # Page d'accueil publique
?   ?   ??? about.php
?   ?   ??? contact.php
?   ?
?   ??? auth/
?   ?   ??? login.php
?   ?   ??? otp_verify.php       # V�rification OTP candidat
?   ?   ??? forgot_password.php
?   ?
?   ??? admin/
?   ?   ??? dashboard.php        # Module Aper�u
?   ?   ??? projects/            # Module Projet (CRUD)
?   ?   ??? criteria/            # Module Crit�res (CRUD)
?   ?   ??? forms/               # Module Formulaire (CRUD + Builder)
?   ?   ?   ??? gallery.php
?   ?   ?   ??? builder.php      # �diteur WYSIWYG drag & drop
?   ?   ?   ??? planning.php     # Calendrier de planification
?   ?   ??? evaluations/         # Module �valuation
?   ?   ?   ??? levels.php
?   ?   ?   ??? score.php
?   ?   ?   ??? ranking.php
?   ?   ??? settings/
?   ?
?   ??? candidate/
?       ??? form_access.php      # Saisie email + OTP
?       ??? form_fill.php        # Remplissage formulaire
?
??? assets/
?   ??? css/
?   ?   ??? tailwind.css         # Build Tailwind ou CDN
?   ?   ??? animate.min.css
?   ?   ??? aos.css
?   ?   ??? custom.css           # Overrides et styles custom
?   ??? js/
?   ?   ??? jquery.min.js
?   ?   ??? jquery-ui.min.js
?   ?   ??? chart.min.js
?   ?   ??? fullcalendar.min.js
?   ?   ??? leaflet.js
?   ?   ??? aos.js
?   ?   ??? tinymce/
?   ?   ??? app.js               # Scripts globaux
?   ?   ??? builder.js           # Logique Form Builder
?   ?   ??? dashboard.js         # Graphiques + stats
?   ?   ??? evaluation.js        # Syst�me de notation
?   ??? img/
?   ??? fonts/
?
??? includes/
?   ??? helpers.php              # Fonctions utilitaires globales
?   ??? security.php             # CSRF, XSS, sanitize
?   ??? upload.php               # Gestion uploads fichiers
?   ??? otp.php                  # G�n�ration / validation OTP
?
??? pdf_templates/               # Templates TCPDF/FPDF
?   ??? form_export.php
?   ??? evaluation_report.php
?   ??? ranking_report.php
?
??? uploads/                     # M�dias upload�s (s�curis�)
?   ??? gallery/
?   ??? forms/
?   ??? logos/
?
??? database/
    ??? criteval_pro.sql         # Script SQL complet (tables + seed)
    ??? migrations/
```

---

## ??? SCH�MA BASE DE DONN�ES

### Tables principales

```sql
-- Utilisateurs (admins + super-admins)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(191) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('superadmin', 'admin') DEFAULT 'admin',
  is_active TINYINT(1) DEFAULT 1,
  avatar VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

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
);

-- Galerie projets
CREATE TABLE project_gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  file_path VARCHAR(255),
  caption VARCHAR(255),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Crit�res d'�valuation
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
);

-- Formulaires
CREATE TABLE forms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  project_id INT,
  layout_json LONGTEXT,         -- Structure JSON du builder WYSIWYG
  status ENUM('draft','published','archived') DEFAULT 'draft',
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Planification formulaires
CREATE TABLE form_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  form_id INT NOT NULL,
  project_id INT,
  access_type ENUM('public_link','email_list','admin_only') DEFAULT 'public_link',
  start_datetime DATETIME,
  end_datetime DATETIME,
  allowed_emails TEXT,          -- JSON array d'emails autoris�s
  allowed_countries TEXT,       -- JSON array de codes pays (ISO)
  is_active TINYINT(1) DEFAULT 1,
  FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
);

-- Soumissions candidats
CREATE TABLE submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  form_id INT NOT NULL,
  candidate_email VARCHAR(191) NOT NULL,
  candidate_name VARCHAR(150),
  country_code VARCHAR(5),
  data_json LONGTEXT,            -- R�ponses du formulaire en JSON
  ip_address VARCHAR(45),
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending','under_review','evaluated','published') DEFAULT 'pending',
  FOREIGN KEY (form_id) REFERENCES forms(id)
);

-- OTP candidats
CREATE TABLE otp_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(191) NOT NULL,
  token VARCHAR(10) NOT NULL,
  form_id INT,
  expires_at DATETIME NOT NULL,
  is_used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Niveaux d'�valuation
CREATE TABLE evaluation_levels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  score_min DECIMAL(5,2),
  score_max DECIMAL(5,2),
  title VARCHAR(100) NOT NULL,
  description TEXT,
  remarks TEXT,
  color_hex VARCHAR(7) DEFAULT '#2EAF7D'
);

-- �valuations
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
);

-- R�sultats finaux
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
);

-- Modules activables/d�sactivables
CREATE TABLE module_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  module_key VARCHAR(50) UNIQUE NOT NULL,  -- 'apercu','projet','critere','formulaire','evaluation'
  is_enabled TINYINT(1) DEFAULT 1,
  label VARCHAR(100),
  icon VARCHAR(50)
);
```

---

## ?? SYST�ME D'AUTHENTIFICATION & S�CURIT�

### R�les et acc�s
- **SuperAdmin** : acc�s total (gestion utilisateurs, modules, param�tres globaux)
- **Admin** : gestion projets, crit�res, formulaires, �valuations (selon modules activ�s)
- **Candidat** : acc�s uniquement au formulaire assign� apr�s authentification OTP

### Flux OTP Candidat
```
1. Candidat arrive sur lien formulaire
2. Saisie de l'email
3. V�rification acc�s (liste blanche / pays / dates actives)
4. G�n�ration OTP 6 chiffres ? envoi email via PHPMailer
5. OTP valide 10 minutes (expiration en base)
6. Apr�s validation OTP ? acc�s formulaire en session s�curis�e
7. � la soumission ? token session invalid�
```

### S�curit� PHP
- CSRF tokens sur tous les formulaires POST
- Sanitisation XSS (htmlspecialchars, strip_tags)
- Requ�tes PDO pr�par�es exclusivement
- Hashage bcrypt pour mots de passe admins
- Rate limiting sur OTP (max 3 tentatives / 15 min)
- .htaccess : interdire acc�s direct aux dossiers `config/`, `models/`, `includes/`
- Headers de s�curit� : `X-Frame-Options`, `Content-Security-Policy`, `X-XSS-Protection`

---

## ??? SECTION 1 - PAGE D'ACCUEIL PUBLIQUE

### Objectif
Pr�sentation professionnelle de Criteval_pro pour les visiteurs, organismes, bailleurs de fonds et candidats africains.

### Structure de la page (d�filement vertical)
```
[NAVBAR FIXE]
  Logo Criteval_pro | Menu (Accueil, Avantages, Fonctionnement, Contact) | Bouton "Se Connecter"

[HERO SECTION - Plein �cran]
  Titre anim� (AOS fade-up) : "L'�valuation de projets, r�invent�e pour l'Afrique"
  Sous-titre : "Criteval_pro centralise vos appels � projets, structure vos crit�res et publie vos classements en toute transparence."
  CTA primaire : "Demander une d�mo" | CTA secondaire : "Voir les fonctionnalit�s"
  Illustration SVG anim�e (dashboard abstrait + carte Afrique Leaflet d�corative)

[AVANTAGES - 6 cartes icon + texte en grid]
  1. �valuation structur�e par crit�res param�trables
  2. Formulaires 100% personnalisables (WYSIWYG)
  3. Notation transparente et tra�able
  4. Classements publi�s en temps r�el
  5. Compatible multi-organisations (Afrique)
  6. Exportation PDF des r�sultats et rapports

[COMMENT �A MARCHE - Timeline horizontale 4 �tapes anim�es]
  �tape 1: Cr�er le projet + crit�res
  �tape 2: Publier le formulaire
  �tape 3: Candidats remplissent et soumettent
  �tape 4: �valuation + Classement publi�

[CARTE AFRIQUE INTERACTIVE - Leaflet.js]
  Affichage des pays africains couverts/disponibles
  Tooltip par pays (nom, statut de disponibilit�)

[STATISTIQUES ANIM�ES - Compteurs jQuery anim�s]
  Ex: XX Projets cr��s | XX Candidatures �valu�es | XX Organisations partenaires

[CTA FINAL - Bandeau color�]
  "Pr�t � lancer votre premier appel � projets ?"
  Bouton "Commencer maintenant"

[FOOTER]
  Liens rapides | Contact | Politique de confidentialit� | � 2025 Criteval_pro
```

---

## ??? SECTION 2 - DASHBOARD ADMINISTRATION

### Layout Dashboard
```
[SIDEBAR GAUCHE fixe - 260px]
  Logo + Nom app
  Navigation modulaire (ic�nes + labels) :
    ?? Aper�u
    ?? Projets
    ? Crit�res
    ?? Formulaires
    ?? �valuations
    ?? Param�tres
  Indicateurs de modules ON/OFF (toggle jQuery UI)
  Avatar admin + D�connexion

[TOPBAR]
  Fil d'ariane | Notifications | Profil admin | Barre de recherche globale

[ZONE CONTENU PRINCIPALE - dynamique par module]
```

---

### 2a. MODULE APER�U (Dashboard overview)

**Widgets (tous toggleables, d�pla�ables via jQuery UI Sortable) :**

```
???????????????????????????????????????????????????????????????????
?  [KPI Card] Projets actifs  ?  [KPI Card] Candidatures  ?  ...  ?
???????????????????????????????????????????????????????????????????
?  [Chart.js] Soumissions par mois (Line chart)                   ?
?  [Chart.js] Scores moyens par projet (Bar chart)                ?
?  [Chart.js] R�partition par pays (Doughnut + Leaflet map mini)  ?
???????????????????????????????????????????????????????????????????
?  [Tableau] Derni�res soumissions        ? [Tableau] Top class�s ?
???????????????????????????????????????????????????????????????????
?  [Calendrier mini] Prochaines �ch�ances formulaires             ?
???????????????????????????????????????????????????????????????????
```

**Fonctionnalit�s :**
- Navigation temporelle (s�lecteur de plage de dates jQuery UI Datepicker)
- Filtres : par projet, par organisation, par statut
- Bouton "Exporter tout" ? PDF (TCPDF) + CSV (PHP natif)
- Actualisation AJAX automatique (jQuery `$.ajax` polling ou manuel)
- Rappels d'�v�nements (formulaires qui ferment bient�t, �valuations en attente)

---

### 2b. MODULE PROJETS (CRUD complet)

**Liste projets :**
- Vue tableau avec pagination + recherche AJAX
- Filtres : statut, organisation, date
- Badges de statut color�s (Brouillon, Actif, Cl�tur�, Archiv�)
- Actions rapides : �diter, Dupliquer, Archiver, Supprimer (avec confirmation modale jQuery UI Dialog)

**Formulaire cr�ation/�dition projet :**
```
Titre *              [Input texte]
Organisation *       [Input texte + autocomplete]
Description          [TinyMCE WYSIWYG - champ riche]
Objectifs/CDC        [TinyMCE WYSIWYG - champ riche]
Statut               [Select : Brouillon / Actif / Cl�tur� / Archiv�]
Galerie              [Uploader images multiples - drag & drop zone jQuery]
                     Aper�u mosa�que des images upload�es
```

**Galerie :**
- Lightbox jQuery pour affichage grande taille
- R�ordonnancement par drag & drop (jQuery UI Sortable)
- Suppression individuelle avec confirmation

---

### 2c. MODULE CRIT�RES (CRUD complet)

**Principe :**
- S�lection du projet cible via `<select>` d�roulant (un seul projet actif � la fois)
- Interface glissez-d�posez pour ordonner les crit�res (jQuery UI Sortable)

**Formulaire crit�re :**
```
Projet li� *         [Select d�roulant - projets existants]
Libell� crit�re *    [Input texte]
Description          [Textarea]
Note maximale *      [Input num�rique - d�faut: 20]
Coefficient/Poids    [Input d�cimal - d�faut: 1.00]
Obligatoire          [Toggle switch jQuery]
Ordre d'affichage    [G�r� automatiquement via drag & drop]
```

**Vue liste crit�res :**
- Tableau r�organisable par drag & drop
- Colonne "Poids total" recalcul�e dynamiquement (jQuery)
- Aper�u de la pond�ration en graphique pie (Chart.js temps r�el)

---

### 2d. MODULE FORMULAIRES

#### Onglet 1 - GALERIE DE FORMULAIRES

- **Vue mosa�que (tiles/cards)** : chaque formulaire affich� en carte avec titre, projet li�, statut, date, actions
- **Bouton "Ajouter"** : en haut � droite - ouvre le Form Builder
- Actions par carte : �diter, Dupliquer, Planifier, Aper�u, Exporter PDF, Supprimer

#### Onglet 2 - FORM BUILDER (�diteur WYSIWYG)

**Architecture :**
```
[SIDEBAR GAUCHE - Variables/Composants disponibles]
  Cat�gories d�pliables :
    ?? Texte       : Titre H1/H2, Paragraphe, �tiquette
    ?? Champs      : Input texte, Email, T�l�phone, Num�rique, Date, Textarea
    ??  S�lection   : Checkbox, Radio, Select, Multi-select
    ?? M�dias      : Upload fichier, Upload image, Signature
    ?? Structure   : Section, S�parateur, Grille 2/3/4 colonnes
    ???  Info        : Alerte info, Avertissement, Badge

[ZONE DE TRAVAIL CENTRALE - Grille magn�tique]
  Canvas avec grille de points (activable/d�sactivable)
  Effet magn�tique snap-to-grid (jQuery UI + logique custom)
  Chaque �l�ment :
    ? D�pla�able (jQuery UI Draggable)
    ? Redimensionnable (jQuery UI Resizable)
    ? Rotatable (handle de rotation custom jQuery)
    ? Scalable (handles de coin)
    ? S�lectionnable (clic) avec panneau de propri�t�s activ�
    ? Multi-s�lection possible (Shift+clic ou rectangle de s�lection)
    ? Groupable/D�groupable
    ? Supprimer (touche Delete ou bouton)
    ? Annuler/R�tablir (Ctrl+Z / Ctrl+Y - pile d'actions jQuery)

[PANNEAU DROIT - Propri�t�s de l'�l�ment s�lectionn�]
  G�om�trie : X, Y, Largeur, Hauteur, Rotation, Scale
  Style : Couleur fond, Couleur bordure, �paisseur bordure, Coins arrondis
  Typographie : Police, Taille, Gras, Italique, Couleur texte, Alignement
  Validation : Requis, Min/Max, Pattern (regex), Message d'erreur custom
  Logique : Conditionnel (afficher SI champ X = valeur Y)
```

**Toolbar haut du Builder :**
```
[Annuler] [R�tablir] | [Grille ON/OFF] [Magn�tisme ON/OFF] | [Aper�u] [Enregistrer brouillon] [Publier]
```

**Rendu final WYSIWYG :**
- Ce que l'admin construit = exactement ce que le candidat voit
- Export PDF du formulaire vide (TCPDF) depuis le builder
- Export PDF du formulaire rempli (depuis une soumission)

#### Onglet 3 - PLANIFICATION

**Calendrier FullCalendar.js :**
- Vue mensuelle / hebdomadaire / liste
- �v�nements color�s par formulaire
- Clic sur cr�neau ? modal de planification

**Modal de planification :**
```
Formulaire *         [Select - liste formulaires publi�s]
Projet associ� *     [Select - projets actifs]

QUI peut acc�der ?
  ? Tout le monde (lien public)
  ? Emails pr�s�lectionn�s [textarea - liste emails, 1 par ligne]
  ? Admins uniquement

QUAND ?
  Date/Heure d�but * [jQuery UI Datetimepicker]
  Date/Heure fin *   [jQuery UI Datetimepicker]

O� ? (Pays Afrique �ligibles)
  [Selectize.js ou Chosen - multi-select avec API Pays Africains]
  [Case "Tous les pays africains"]

Statut             [Toggle Actif/Inactif]
[Enregistrer]
```

---

### 2e. MODULE �VALUATIONS

#### Sous-module A - Niveaux d'�valuation (CRUD)
```
Titre *              [Input texte - ex: "Excellent", "Insuffisant"]
Note minimale *      [Input num�rique]
Note maximale *      [Input num�rique]
Description          [Textarea]
Remarques            [Textarea]
Couleur de niveau    [Color picker jQuery UI]
```

#### Sous-module B - Notation des soumissions

**Workflow :**
```
1. S�lectionner un projet [Select]
2. Liste des soumissions en attente d'�valuation (tableau pagin�)
3. Clic sur "�valuer" ? page d'�valuation d'une soumission
```

**Page d'�valuation d'une soumission :**
```
[Colonne gauche] R�ponses du candidat (lecture seule - formulaire rempli)
[Colonne droite] Crit�res du projet + champs de notation

Pour chaque crit�re :
  - Libell� du crit�re
  - Note /[max_score]   [Input jQuery UI Slider + valeur num�rique synchronis�e]
  - Commentaire         [Textarea]
  - Indicateur visuel de la pond�ration

[Score final calcul� dynamiquement en temps r�el - jQuery]
[Indicateur circulaire SVG anim� - �l�ment signature Criteval_pro]
[Bouton "Enregistrer �valuation"]
```

#### Sous-module C - Classements

**Tableau de classement par projet :**
- Tri automatique par score pond�r� d�croissant
- Rang affich� avec m�dailles ?????? pour top 3
- Historique des classements dans le temps (Chart.js �volution de rang)
- Filtres : par date d'�valuation, par niveau
- Actions : **Publier/D�publier r�sultat** par candidat ou en masse
- Export PDF du classement complet (TCPDF - rapport officiel mis en page)
- Export CSV

---

## ?? SECTION 3 - FORMULAIRE CANDIDAT

### Flux d'acc�s
```
URL unique du formulaire (ex: /formulaire/{token_unique})
  ?
[Page d'accueil formulaire]
  - Logo Criteval_pro + Titre du projet
  - Description de l'appel � candidature
  - Champ email *
  - Bouton "Recevoir mon code d'acc�s"
  ?
[V�rification acc�s (PHP)]
  - Formulaire actif ? (date + heure)
  - Pays de l'IP autoris� ?
  - Email dans liste blanche (si restreint) ?
  ?
[Page OTP]
  - "Un code � 6 chiffres a �t� envoy� � {email}"
  - 6 inputs s�par�s (auto-focus suivant jQuery)
  - Timer compte � rebours 10 minutes (jQuery)
  - Lien "Renvoyer le code" (apr�s 60s)
  ?
[Formulaire de remplissage]
  - Rendu exact du WYSIWYG builder
  - Sauvegarde automatique toutes les 2 minutes (localStorage + AJAX)
  - Barre de progression (% compl�t� jQuery)
  - Validation c�t� client (jQuery Validation Plugin) + c�t� serveur (PHP)
  - Upload de pi�ces jointes si champs pr�sents
  - Bouton "Soumettre ma candidature"
  ?
[Page de confirmation]
  - Num�ro de r�f�rence unique de la soumission
  - Option de t�l�charger son formulaire rempli (PDF TCPDF)
  - Email de confirmation automatique (PHPMailer)
```

---

## ?? SYST�ME D'EMAILING

### Templates emails (HTML responsive, PHPMailer)

| �v�nement | Destinataire | Template |
|---|---|---|
| OTP code d'acc�s | Candidat | `otp_email.php` |
| Confirmation soumission | Candidat | `submission_confirm.php` |
| R�sultat publi� | Candidat | `result_published.php` |
| Nouvelle soumission re�ue | Admin(s) | `admin_new_submission.php` |
| Rappel fermeture formulaire | Admin | `form_closing_reminder.php` |
| Cr�ation compte admin | Admin | `welcome_admin.php` |

### Configuration SMTP (config/mailer.php)
```php
define('SMTP_HOST',     'smtp.hostinger.com');
define('SMTP_USER',     'noreply@votredomaine.com');
define('SMTP_PASS',     'VOTRE_MOT_DE_PASSE');
define('SMTP_PORT',     587);
define('SMTP_SECURE',   'tls');
define('MAIL_FROM',     'noreply@votredomaine.com');
define('MAIL_FROM_NAME','Criteval Pro');
```

---

## ??? INT�GRATION API PAYS AFRICAINS

### Source
- **REST Countries API** filtr� Afrique : `https://restcountries.com/v3.1/region/africa`
- OU **API africaine d�di�e** (African Countries API) - pr�f�rer source avec donn�es en fran�ais

### Utilisation dans l'app
```javascript
// Chargement asynchrone jQuery (mis en cache localStorage 24h)
function loadAfricanCountries() {
  const cached = localStorage.getItem('african_countries');
  const cacheTime = localStorage.getItem('african_countries_time');
  const now = Date.now();

  if (cached && cacheTime && (now - cacheTime) < 86400000) {
    return Promise.resolve(JSON.parse(cached));
  }

  return $.getJSON('https://restcountries.com/v3.1/region/africa')
    .then(data => {
      const countries = data.map(c => ({
        code: c.cca2,
        name: c.translations?.fra?.common || c.name.common,
        flag: c.flags?.svg,
        latlng: c.latlng
      })).sort((a,b) => a.name.localeCompare(b.name, 'fr'));
      localStorage.setItem('african_countries', JSON.stringify(countries));
      localStorage.setItem('african_countries_time', now);
      return countries;
    });
}
```

### Utilisation Leaflet.js
- Carte d'accueil publique : pays africains mis en �vidence, tooltip par pays
- Carte dans le planning formulaires : visualisation zones cibl�es
- Carte dans le dashboard : r�partition g�ographique des soumissions (choropl�th via GeoJSON Afrique)

---

## ?? G�N�RATION PDF (TCPDF / FPDF)

### Types de documents g�n�r�s

1. **Formulaire vide (gabarit)** - � imprimer/distribuer
2. **Formulaire rempli d'un candidat** - fid�le au WYSIWYG
3. **Rapport d'�valuation individuel** - score par crit�re + score final + niveau
4. **Rapport de classement complet** - tous les candidats d'un projet class�s
5. **Export donn�es dashboard** - r�sum� statistique avec graphiques (Chart.js ? image via `html2canvas` + int�gration TCPDF)

### En-t�tes PDF standards
- Logo Criteval_pro + nom de l'organisation
- Titre du document + r�f�rence unique
- Date et heure de g�n�ration
- Pied de page : pagination + "Document g�n�r� par Criteval_pro"

---

## ?? PARAM�TRES ADMINISTRATION

```
[Informations g�n�rales]
  - Nom de l'application (configurable)
  - Logo (upload)
  - Adresse / Contact / Site web

[Gestion modules]
  - Toggle ON/OFF par module (Aper�u, Projets, Crit�res, Formulaires, �valuations)
  - Chaque d�sactivation masque le module dans la sidebar et redirige

[Gestion utilisateurs admin]
  - CRUD complet (SuperAdmin uniquement)
  - Assignation de r�le

[Param�tres email]
  - Test SMTP en live
  - Personnalisation du footer email

[S�curit�]
  - Dur�e de vie session (minutes)
  - Max tentatives OTP
  - Activation/d�sactivation logs d'acc�s
```

---

## ?? INTERACTIONS JQUERY - COMPORTEMENTS ATTENDUS

```javascript
// 1. Sidebar toggleable (mobile + desktop)
$('#sidebarToggle').on('click', function() {
  $('#sidebar').toggleClass('collapsed');
  $('#mainContent').toggleClass('sidebar-collapsed');
});

// 2. Modules drag & drop dans le dashboard Aper�u
$('#widgetContainer').sortable({
  handle: '.widget-handle',
  animation: 150,
  update: function() { saveWidgetOrder(); }
});

// 3. Confirmations de suppression
$('.btn-delete').on('click', function(e) {
  e.preventDefault();
  const url = $(this).data('url');
  $('<div>').text('Confirmer la suppression ?').dialog({
    buttons: {
      'Supprimer': function() { window.location = url; },
      'Annuler': function() { $(this).dialog('close'); }
    }
  });
});

// 4. Sliders de notation synchronis�s
$('.score-slider').each(function() {
  const input = $(this).siblings('.score-input');
  $(this).slider({
    min: 0, max: parseFloat($(this).data('max')),
    step: 0.5,
    slide: function(e, ui) {
      input.val(ui.value);
      updateFinalScore();
    }
  });
});

// 5. Auto-save formulaire candidat
setInterval(function() {
  if ($('#candidateForm').length) {
    const data = $('#candidateForm').serialize();
    $.post('/api/autosave', { data: data, ref: FORM_REF }, function(res) {
      $('#autosave-status').text('Sauvegard� � ' + new Date().toLocaleTimeString());
    });
  }
}, 120000);
```

---

## ?? D�PLOIEMENT

### Local (d�veloppement)
```
Serveur : XAMPP / Laragon (PHP 8.x + MySQL 8.x)
URL     : http://localhost/criteval_pro/
Config  : config/config.php ? APP_ENV = 'development'
```

### Production (Hostinger cPanel)
```
PHP     : 8.1+ (via cPanel PHP Selector)
MySQL   : Cr�er base via phpMyAdmin + importer criteval_pro.sql
Dossier : public_html/criteval_pro/ OU sous-domaine criteval.votredomaine.com
HTTPS   : Certificat SSL Let's Encrypt (1-clic Hostinger)
.env    : Variables sensibles hors du d�p�t Git
Cron    : T�che cron Hostinger ? rappels formulaires fermants (daily 08:00)
```

### S�curit� .htaccess (racine)
```apache
Options -Indexes
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Bloquer acc�s direct aux dossiers sensibles
RewriteRule ^(config|models|includes|database)/ - [F,L]

# Headers s�curit�
Header set X-Frame-Options "SAMEORIGIN"
Header set X-Content-Type-Options "nosniff"
Header set X-XSS-Protection "1; mode=block"
```

---

## ?? ORDRE DE D�VELOPPEMENT RECOMMAND�

```
Phase 1 - Fondations (Semaine 1-2)
  ? Structure dossiers + config + router PHP
  ? Base de donn�es SQL compl�te
  ? Auth admin (login, sessions, r�les)
  ? Layout admin + sidebar + topbar

Phase 2 - Modules core (Semaine 3-5)
  ? Module Projets (CRUD complet + galerie)
  ? Module Crit�res (CRUD + pond�ration)
  ? Module Niveaux d'�valuation

Phase 3 - Form Builder (Semaine 6-8)
  ? Galerie formulaires
  ? Form Builder WYSIWYG (drag & drop jQuery UI)
  ? Planification FullCalendar

Phase 4 - Candidats (Semaine 9-10)
  ? Flux OTP + acc�s formulaire
  ? Rendu formulaire candidat
  ? Soumission + confirmation email

Phase 5 - �valuation (Semaine 11-12)
  ? Interface de notation par crit�res
  ? Calcul score pond�r�
  ? Classements + publication

Phase 6 - Dashboard + PDF (Semaine 13-14)
  ? Module Aper�u (graphiques Chart.js)
  ? Exports PDF (TCPDF - tous types)
  ? Exports CSV

Phase 7 - Page publique + finitions (Semaine 15-16)
  ? Page d'accueil publique compl�te
  ? Carte Leaflet Afrique
  ? Param�tres admin
  ? Tests complets + corrections
  ? D�ploiement Hostinger
```

---

## ? CHECKLIST DE QUALIT� FINALE

Avant livraison, v�rifier :
- [ ] Application 100% responsive (mobile, tablette, desktop)
- [ ] Tous les formulaires prot�g�s CSRF
- [ ] Aucune requ�te SQL sans PDO pr�par�
- [ ] PDF g�n�r�s correctement (tous les types)
- [ ] Emails re�us (OTP, confirmation, r�sultat)
- [ ] Form Builder sauvegarde et restitue fid�lement le layout
- [ ] Classements recalcul�s automatiquement � chaque nouvelle �valuation
- [ ] Modules activables/d�sactivables fonctionnels
- [ ] Export CSV Dashboard fonctionne
- [ ] Carte Leaflet charg�e et interactive
- [ ] API pays africains charg�e et mise en cache
- [ ] .htaccess en place (r��criture + protection dossiers)
- [ ] SSL activ� en production Hostinger
- [ ] Aucun `var_dump`, `echo`, ou `die` de debug en production

---

*Prompt r�dig� pour Criteval_pro - Version 1.0 - D�veloppement local puis d�ploiement cPanel Hostinger*
*Stack: PHP 8.x natif | MySQL | jQuery | jQuery UI | Tailwind CSS | Chart.js | Leaflet.js | TCPDF | PHPMailer | FullCalendar | TinyMCE | AOS | Animate.css*
