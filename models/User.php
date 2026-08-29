<?php
declare(strict_types=1);

class User
{
    public static function findByCredential(string $credential): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, username, name, email, password, role, is_active, avatar, permissions
             FROM users
             WHERE (username = :username OR email = :email)
             LIMIT 1'
        );
        $stmt->execute([
            'username' => $credential,
            'email' => $credential,
        ]);
        $user = $stmt->fetch();

        return is_array($user) ? $user : null;
    }

    public static function authenticate(string $credential, string $password): ?array
    {
        $user = self::findByCredential($credential);
        if (!$user || (int) $user['is_active'] !== 1) {
            return null;
        }

        if (!password_verify($password, (string) $user['password'])) {
            return null;
        }

        unset($user['password']);
        return $user;
    }

    public static function all(): array
    {
        $stmt = db()->query('SELECT id, username, name, email, role, is_active, avatar, permissions FROM users ORDER BY id DESC');
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare('INSERT INTO users (username, name, email, password, role, is_active, avatar, permissions) VALUES (:username, :name, :email, :password, :role, :is_active, :avatar, :permissions)');
        $passwordHash = isset($data['password']) && $data['password'] !== '' ? password_hash($data['password'], PASSWORD_DEFAULT) : null;
        $permissions = isset($data['permissions']) ? json_encode($data['permissions']) : null;
        $stmt->execute([
            'username' => $data['username'] ?? null,
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $passwordHash,
            'role' => $data['role'] ?? 'admin',
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'avatar' => $data['avatar'] ?? null,
            'permissions' => $permissions,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];
        if (isset($data['username'])) { $fields[] = 'username = :username'; $params['username'] = $data['username']; }
        if (isset($data['name'])) { $fields[] = 'name = :name'; $params['name'] = $data['name']; }
        if (isset($data['email'])) { $fields[] = 'email = :email'; $params['email'] = $data['email']; }
        if (isset($data['password']) && $data['password'] !== '') { $fields[] = 'password = :password'; $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT); }
        if (isset($data['role'])) { $fields[] = 'role = :role'; $params['role'] = $data['role']; }
        if (isset($data['is_active'])) { $fields[] = 'is_active = :is_active'; $params['is_active'] = (int) $data['is_active']; }
        if (array_key_exists('avatar', $data)) { $fields[] = 'avatar = :avatar'; $params['avatar'] = $data['avatar']; }
        if (isset($data['permissions'])) { $fields[] = 'permissions = :permissions'; $params['permissions'] = json_encode($data['permissions']); }

        if (empty($fields)) return false;

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = db()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public static function setRoleAndPermissions(int $id, string $role, array $permissions): bool
    {
        $stmt = db()->prepare('UPDATE users SET role = :role, permissions = :permissions WHERE id = :id');
        return $stmt->execute([
            'role' => $role,
            'permissions' => json_encode($permissions),
            'id' => $id,
        ]);
    }
}
