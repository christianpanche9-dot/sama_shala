-- Migración 0009: reseña/biografía del profesor.
ALTER TABLE profesores
  ADD COLUMN resena TEXT DEFAULT NULL AFTER especialidad;
