-- Migración 0003: clasificar las actividades como clase, evento o terapia.
ALTER TABLE actividades
  ADD COLUMN tipo ENUM('clase','evento','terapia') NOT NULL DEFAULT 'clase' AFTER categoria;
