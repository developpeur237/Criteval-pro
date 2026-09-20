# Criteval Pro — production baseline

## Canonical vocabulary

The user-facing domain is organisation-based:

- **Organisation**: the evaluated entity and its profile.
- **Réalisation**: an activity or result supported by evidence.
- **Critère**: a measurable evaluation rule.
- **Soumission**: an organisation's completed response to a form.
- **Session**: a planned training event published at `/session/{slug}`.

The legacy SQLite table/column names (`projects`, `project_id`) remain as an internal compatibility layer. They must not be presented as “projects” in active user journeys.

## Deployment checklist

1. Set `APP_ENV=production` and configure `BASE_URL` for the HTTPS origin.
2. Keep `storage/`, `uploads/`, and the SQLite database outside the public web root where possible. If that is not possible, keep the checked-in deny rules in place.
3. Use HTTPS, a reverse-proxy request limit, and a PHP upload limit compatible with the evidence policy (10 MB per file).
4. Change the seeded administrator password immediately and create named accounts with least-privilege permissions.
5. Configure SMTP through the server-side mail settings; never put SMTP credentials in JavaScript or committed source.
6. Back up the SQLite file before upgrades and verify a restore regularly. For multi-worker/high-volume deployments, migrate the PDO adapter to MySQL/MariaDB and retain the same service contracts.
7. Run `php -l` over all deployed PHP files and exercise the complete flow: organisation → criteria → form → scheduled access → OTP → submission/evidence → evaluation → ranking → session.

## Production invariants

- Every state-changing request is CSRF protected.
- Administrative routes require both an administrative role and module/action permission.
- Evaluation scores are rejected when outside the criterion maximum; they are never silently clamped.
- Required criteria must have evidence before evaluation.
- Evidence is stored with generated names and is streamed only through an authorised endpoint.
- Ranking ties are deterministic: weighted score, submission timestamp, then submission ID.
