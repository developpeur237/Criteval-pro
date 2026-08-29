ALTER TABLE users
  MODIFY role ENUM('superadmin', 'admin', 'user', 'visitor') NOT NULL DEFAULT 'admin';
