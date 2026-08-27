-- Migración 0015: agrega "taller" como nuevo tipo de actividad.
ALTER TABLE actividades
  MODIFY COLUMN tipo ENUM('clase','evento','terapia','taller') NOT NULL DEFAULT 'clase';
