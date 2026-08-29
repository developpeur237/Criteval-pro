SET @column_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'username'
);

SET @sql := IF(
  @column_exists = 0,
  'ALTER TABLE users ADD COLUMN username VARCHAR(100) NULL AFTER id, ADD UNIQUE INDEX users_username_unique (username)',
  'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE users
SET username = 'admin',
    name = 'Super Administrateur',
    email = 'admin@criteval.pro',
    password = '$2y$12$6K5WXKXlx2o4drPLAVAZOOucu7zSpO/9.hqn55Rb7axu.Pnrh8RB2',
    role = 'superadmin',
    is_active = 1
WHERE id = 1;

INSERT INTO users (username, name, email, password, role, is_active)
SELECT 'admin', 'Super Administrateur', 'admin@criteval.pro', '$2y$12$6K5WXKXlx2o4drPLAVAZOOucu7zSpO/9.hqn55Rb7axu.Pnrh8RB2', 'superadmin', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');
