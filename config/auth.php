<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams['lifetime'],
        'path' => $cookieParams['path'] ?: '/',
        'domain' => $cookieParams['domain'],
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function permission_module_keys(): array
{
    return [
        'apercu',
        'projets',
        'criteres',
        'formulaires',
        'formation',
        'evaluations',
        'classements',
        'calendrier',
        'parametres',
        'utilisateurs',
    ];
}

function permission_module_labels(): array
{
    return [
        'apercu' => 'Aperçu',
        'projets' => 'Organisations',
        'criteres' => 'Critères',
        'formulaires' => 'Formulaires',
        'formation' => 'Formation',
        'evaluations' => 'Évaluations',
        'classements' => 'Classements',
        'calendrier' => 'Planning',
        'parametres' => 'Paramètres',
        'utilisateurs' => 'Utilisateurs',
    ];
}

function permission_actions(): array
{
    return ['view', 'create', 'update', 'delete'];
}

function permission_action_labels(): array
{
    return [
        'view' => 'Voir',
        'create' => 'Créer',
        'update' => 'Modifier',
        'delete' => 'Supprimer',
    ];
}

function available_roles(): array
{
    return ['superadmin', 'admin', 'user', 'visitor'];
}

function role_labels(): array
{
    return [
        'superadmin' => 'Super administrateur',
        'admin' => 'Administrateur',
        'user' => 'Utilisateur',
        'visitor' => 'Visiteur',
    ];
}

function role_label(string $role): string
{
    $role = strtolower($role);
    $labels = role_labels();

    return $labels[$role] ?? ucfirst($role);
}

function normalize_permissions_structure($permissions): array
{
    if (is_string($permissions) && $permissions !== '') {
        $decoded = json_decode($permissions, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $permissions = $decoded;
        }
    }

    if (!is_array($permissions)) {
        return [];
    }

    $modules = [];
    $sourceModules = $permissions['modules'] ?? $permissions;
    if (!is_array($sourceModules)) {
        return [];
    }

    foreach (permission_module_keys() as $module) {
        $raw = $sourceModules[$module] ?? null;
        $normalized = ['view' => false, 'create' => false, 'update' => false, 'delete' => false];

        if (is_bool($raw) || is_int($raw) || is_string($raw)) {
            $normalized['view'] = (bool) $raw;
        } elseif (is_array($raw)) {
            $normalized['view'] = !empty($raw['view'] ?? $raw['voir'] ?? false);
            $normalized['create'] = !empty($raw['create'] ?? $raw['creer'] ?? false);
            $normalized['update'] = !empty($raw['update'] ?? $raw['modifier'] ?? false);
            $normalized['delete'] = !empty($raw['delete'] ?? $raw['supprimer'] ?? false);
        }

        $modules[$module] = $normalized;
    }

    return ['modules' => $modules];
}

function default_permissions_for_role(string $role): array
{
    $role = strtolower($role);
    $defaults = ['modules' => []];
    foreach (permission_module_keys() as $module) {
        $defaults['modules'][$module] = [
            'view' => false,
            'create' => false,
            'update' => false,
            'delete' => false,
        ];
    }

    if (in_array($role, ['admin', 'superadmin'], true)) {
        foreach ($defaults['modules'] as $module => $actions) {
            foreach ($actions as $action => $value) {
                $defaults['modules'][$module][$action] = true;
            }
        }
        return $defaults;
    }

    if ($role === 'user') {
        foreach (['apercu', 'projets', 'criteres', 'formulaires', 'formation', 'evaluations', 'classements', 'calendrier'] as $module) {
            $defaults['modules'][$module]['view'] = true;
        }
        return $defaults;
    }

    if ($role === 'visitor') {
        $defaults['modules']['apercu']['view'] = true;
        return $defaults;
    }

    return $defaults;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'] ?? null,
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'avatar' => $user['avatar'] ?? null,
        'permissions' => normalize_permissions_structure($user['permissions'] ?? null),
    ];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function has_role(string $role): bool
{
    $user = current_user();
    return $user && isset($user['role']) && $user['role'] === $role;
}

function is_admin(): bool
{
    // Only administrative roles may access the server-side administration
    // surface.  User/visitor permissions are intentionally read-only and are
    // enforced by the public/candidate workflows instead of admin endpoints.
    return has_role('admin') || has_role('superadmin');
}

function is_superadmin(): bool
{
    return has_role('superadmin');
}

function require_admin(?string $module = null, string $action = 'view'): void
{
    if (!is_admin()) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    if ($module !== null && !can_module_action($module, $action)) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}

/** Fail closed for API/state-changing routes when a permission is missing. */
function require_permission(string $module, string $action = 'view'): void
{
    require_admin($module, $action);
}

function module_access_by_role(): array
{
    return [
        'superadmin' => [
            'apercu',
            'projets',
            'criteres',
            'formulaires',
            'formation',
            'evaluations',
            'classements',
            'calendrier',
            'parametres',
            'utilisateurs',
        ],
        'admin' => [
            'apercu',
            'projets',
            'criteres',
            'formulaires',
            'evaluations',
            'classements',
            'calendrier',
            'parametres',
            'utilisateurs',
        ],
        'user' => [
            'apercu',
            'projets',
            'criteres',
            'formulaires',
            'formation',
            'evaluations',
            'classements',
            'calendrier',
        ],
        'visitor' => [
            'apercu',
        ],
    ];
}

function can_view_module(string $module): bool
{
    if (!is_admin()) {
        return false;
    }

    $user = current_user();
    $role = $user['role'] ?? '';
    $map = module_access_by_role();

    if ($user && isset($user['permissions'])) {
        $perms = normalize_permissions_structure($user['permissions']);
        if (isset($perms['modules'][$module]['view'])) {
            return (bool) $perms['modules'][$module]['view'];
        }
    }

    return in_array($module, $map[$role] ?? [], true);
}

function can_module_action(string $module, string $action = 'view'): bool
{
    if (!is_admin()) {
        return false;
    }

    $action = strtolower($action);
    if (!in_array($action, permission_actions(), true)) {
        $action = 'view';
    }

    $user = current_user();
    $role = $user['role'] ?? '';

    if ($user && isset($user['permissions'])) {
        $perms = normalize_permissions_structure($user['permissions']);
        if (isset($perms['modules'][$module][$action])) {
            return (bool) $perms['modules'][$module][$action];
        }
    }

    $defaults = default_permissions_for_role($role);
    return (bool) ($defaults['modules'][$module][$action] ?? false);
}
