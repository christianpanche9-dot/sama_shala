-- Migración 0011: foto del profesor.
ALTER TABLE profesores
  ADD COLUMN imagen VARCHAR(255) DEFAULT NULL AFTER username;
