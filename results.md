# Criteval Pro — application scan

**Scan date:** 2026-09-20  
**Scope:** PHP entry point and routing, controllers/models, views/assets, SQLite schema and migrations, configuration/security helpers, documentation, and repository status.  
**Observed stack:** Native PHP 7.4.19 CLI, PDO SQLite, server-rendered PHP views, jQuery/jQuery UI, Tailwind/CSS, Chart.js, FullCalendar, Leaflet, PHPMailer.

## Executive outcome

Criteval Pro is a web platform for managing an organisation-based application/evaluation programme. It collects organisation submissions through configurable forms, accepts evidence per evaluation criterion, lets authenticated staff score submissions, calculates weighted results, ranks candidates, and supports training-session planning and attendance. The public surface also exposes programme information and public training-session links.

The current implementation is a functional MVP with a broad feature surface. The strongest implemented path is: organisation/form setup → scheduled candidate access with email OTP → submission and evidence storage → criterion scoring → result/ranking data → dashboard reporting. It is not yet a production-ready SaaS: the codebase contains legacy/static UI alongside the active application, terminology is inconsistent, some promised features are stubs, and authorization/operational hardening need attention before deployment.

## Purpose

The product purpose is to make an evaluation or funding-call workflow auditable and repeatable for African organisations. It centralises:

- organisation profiles and programme metadata;
- reusable and custom weighted criteria;
- configurable application forms and schedules;
- candidate/organisation submissions and criterion-specific evidence;
- evaluator scores and comments;
- weighted results, ranking, and publication state;
- training sessions, participant attendance, and optional training grades;
- administrative dashboards, notifications, email, and database export/import.

The public copy still describes the product partly as a “financing projects” tool, while the current domain model and newer UI describe organisations. This indicates an in-progress domain migration rather than a fully normalised product vocabulary.

## Objectives inferred from the code and documentation

1. Replace an informal/manual evaluation process with a central system of record.
2. Apply a consistent set of CS4ME-style organisation-performance criteria (the schema seeds ten templates covering participation, responsiveness, communication, finance, reporting, recruitment, field work, advocacy, capacity building, and peer mentoring).
3. Collect evidence for each criterion so scores are supportable.
4. Make scoring configurable through criterion weights and maximum scores.
5. Provide transparent, time-aware selection through submission timestamps and rankings.
6. Plan and publish training sessions with a public `/session/{slug}` link.
7. Support controlled access using sessions, roles/permissions, CSRF tokens, password hashing, and email OTP for candidate form access.
8. Produce an installable local deployment using SQLite, with a stated future path toward MySQL/MariaDB.

## Current workflow

### Administrator/staff workflow

1. **Install/bootstrap:** `index.php` checks whether SQLite is ready, creates/updates the schema, seeds the initial superadmin and criteria templates, then redirects to login.
2. **Authenticate:** staff log in with username/email and password. Session IDs are regenerated on login.
3. **Create an organisation/programme record:** the active CRUD is routed through `admin/projects` and stored in the `projects` table, although the visible product language is increasingly “organisation”. Fields include domains, target audiences, legal status, country, contact details, budget, duration, logo, and lifecycle status.
4. **Configure criteria:** select seeded templates and/or create custom criteria, set weights/max scores, and associate them with the organisation record.
5. **Build a form:** save a JSON layout, associate it with an organisation/programme, and set draft/published/archived status.
6. **Schedule access:** define opening/closing dates, access mode (public link, email list, or admin only), allowed emails/countries, and active state.
7. **Plan training:** create a named session with objective, date, capacity, format, linked organisation, public slug, and optional evaluation weight; manage attendance/grade records.
8. **Review submissions:** candidate submissions appear in evaluation queues and dashboard counts.
9. **Evaluate:** enter one score/comment per criterion. The model writes evaluations, marks the submission evaluated, and upserts a result with total and weighted score.
10. **Rank/publish/report:** ranking data is available to the ranking view and result records have publication fields. PDF template files exist, but the report/export implementation is incomplete (see gaps below).

### Candidate/organisation workflow

1. Open a form access page or scheduled/public form.
2. Enter an email and request an OTP.
3. Verify the six-digit OTP; the form ID and candidate email are retained in the session.
4. Fill the dynamic form and submit JSON data.
5. Upload evidence files keyed by criterion; evidence is stored under `storage/evidence` and linked in `organization_evidence`.
6. Receive a submission confirmation email (or queue/failure status depending on mail configuration).

### Data flow

`users → organisations/projects → criteria/templates → forms → schedules → OTP access → submissions + evidence → evaluations → results/ranking`, with training sessions/attendance connected to submissions.

## What is working well

- A single front controller provides a coherent route surface and centralises bootstrap checks.
- PDO prepared statements and SQLite foreign-key enforcement are used throughout the active models.
- The schema includes the important domain entities: forms, schedules, submissions, OTPs, criteria, evidence, evaluations, results, training sessions, and attendance.
- Criteria are reusable via templates and can be customised per organisation/programme.
- CSRF checks are present on most state-changing admin endpoints; passwords and OTP values are hashed.
- Schedule validation checks date ordering and form/organisation consistency.
- Evidence is linked to a specific submission and criterion, enabling criterion-level auditability.
- Database export/import includes a pre-import backup attempt.
- All scanned PHP files passed `php -l`; no parse errors were found.

## Findings and improvement suggestions

### P0 — address before production

1. **Enforce authorization server-side per action/module.** `require_admin()` currently accepts every authenticated role (`visitor`, `user`, `admin`, and `superadmin`), while many POST handlers only call `require_admin()`. `can_module_action()` exists but is not consistently enforced at the endpoint. A visitor/user could therefore reach administrative mutations if they can construct requests. Add explicit policy checks such as `require_permission('evaluations', 'update')`, apply them to every GET/POST/API route, and test each role.
2. **Move secrets and environment configuration out of tracked PHP/config data.** `APP_ENV` is hard-coded to `development`; SMTP/database settings and seed credentials require production-safe handling. Use environment variables or a non-public config file, rotate the seeded administrator credential on install, disable verbose errors, and set secure session cookie attributes (`Secure`, `HttpOnly`, `SameSite`).
3. **Harden uploads and evidence access.** Validate evidence MIME/content, extension, size, and per-request count; generate non-guessable names; prevent executable uploads; store outside the web root where possible; and add an authenticated download endpoint that checks submission/criterion permissions. The current evidence path is recorded as a web-relative path but there is no clear controlled download flow.
4. **Add transaction and validation boundaries around evaluation.** Validate each score against the criterion’s `max_score`, verify that every submitted criterion belongs to the submission’s organisation/form, reject duplicates/incomplete required criteria according to policy, and wrap score/result writes in a transaction. Define deterministic tie-breaking (score, then submission timestamp, then ID) and compute/persist rank positions when publishing.
5. **Back up and migrate the database safely.** SQLite is appropriate for local MVP use, but the project brief targets MySQL/MariaDB production. Add a tested migration system, scheduled backups/restore drills, locking/concurrency guidance, and a documented MySQL adapter/schema compatibility plan.

### P1 — product correctness and maintainability

6. **Finish the organisation terminology migration.** Rename internal concepts/routes/tables gradually or introduce an explicit domain mapping (`organisations`, `calls`, `submissions`) while preserving compatibility. Remove contradictory labels such as “Projets actifs”, “appel à projets”, and legacy project forms where the intended entity is an organisation.
7. **Separate controllers from the front controller.** The `controllers/*.php` classes are mostly thin view wrappers; most business routing and mutations remain in `index.php`. Move route handlers into controller methods, add a small router/request-response layer, and keep SQL/business rules in models/services.
8. **Remove or isolate legacy/static duplicates.** Multiple `templates/legacy_*.html` files contain old labels and mock interactions. Keep them in an archive outside the deployable web tree or delete them after confirming they are unused; otherwise they will continue to confuse maintenance and users.
9. **Complete unfinished modules.** PDF templates currently return minimal placeholder HTML; `FormField` is empty; evaluation levels/publication controls and several dashboard areas appear skeletal; there is no visible automated test suite. Define acceptance criteria for PDF/CSV exports, levels, publication, notifications, and the form builder, then implement integration tests for the end-to-end workflow.
10. **Strengthen form/schema integrity.** Enforce foreign keys consistently (including `forms.project_id`, `results.project_id`, and criteria relationships), add unique/index constraints for common lookups, validate JSON layout schema instead of only checking that JSON parses, and store allowed email/country lists as structured data with clear normalization rules.
11. **Improve observability.** Add structured audit logs for login, permission changes, score changes, publication, imports, and evidence access; include correlation/request IDs; expose health checks; and make mail queue failures/retries visible to administrators.

### P2 — usability and operational polish

12. **Clarify user journeys and status semantics.** Use one language (French or bilingual with a glossary), distinguish organisation, funding call, application, and training session in navigation, and show an explicit status timeline from draft → open → submitted → under review → evaluated → published.
13. **Make ranking transparent.** Show criterion-level score breakdowns, weights, evidence links, submission timestamp, tie-break rule, evaluator identity, and publication timestamp. Add an immutable published snapshot so later criterion edits do not rewrite historical results.
14. **Improve candidate resilience.** Add autosave/draft recovery, resumable uploads, clear per-file validation errors, duplicate-submission policy, accessibility labels, mobile testing, and a confirmation page with a reference number.
15. **Add automated quality gates.** Introduce PHPUnit or a lightweight integration test harness, static analysis, CS fixer/linter, migration tests, role/permission matrix tests, upload security tests, and a CI job that runs PHP syntax checks and the test suite.
16. **Document deployment.** Provide a concise README covering PHP extensions (PDO SQLite now; PDO MySQL for target production), web-server rewrite rules, writable directories, cron/mail queue operation, backup/restore, HTTPS, and first-login credential rotation.

## Recommended next sequence

1. Lock down endpoint authorization and upload/download security.
2. Add evaluation validation/transactions and deterministic ranking publication.
3. Add an end-to-end test fixture for install → form → OTP → submission/evidence → evaluation → ranking.
4. Decide and document the canonical domain vocabulary (organisation vs project) and remove legacy UI contradictions.
5. Finish exports, publication snapshots, audit logs, and production deployment/migration documentation.

## Limitations of this scan

- This was a static code/configuration review; no browser acceptance test or live mail delivery test was run.
- PHP syntax checks passed, but runtime behavior depends on the configured web server, PHP extensions, writable directories, and database contents.
- The `sqlite3` command-line utility was unavailable in the shell, so live row counts were not independently queried; schema and model inspection were used instead.
- The repository had substantial pre-existing modified/untracked files at scan time; this report does not attribute those changes to a specific author or commit.

## Follow-up implementation completed after this scan

The application was subsequently updated to address the highest-impact findings: administrative routes now require administrative roles and module/action permissions; session cookies are hardened; form layouts are schema-validated before persistence; the drag-and-drop builder supports stable technical field names and option lists; candidate pages render the saved form layout dynamically; required criterion evidence is checked and restricted to the form’s organisation; evidence is served through an authorization-checked endpoint; repeated evaluations replace prior criterion scores instead of inflating totals; and ranking positions are refreshed with score/timestamp/ID tie-breaking. Active public and dashboard copy was also normalized around organisations and organisation-realization evidence.
