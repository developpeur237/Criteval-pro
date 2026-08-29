-- Migration: add permissions column to users for per-user module visibility
ALTER TABLE users
  ADD COLUMN permissions LONGTEXT NULL AFTER avatar;

-- permissions will store a JSON object like: {"modules": {"apercu":1, "projets":0}, "can_manage": true}
