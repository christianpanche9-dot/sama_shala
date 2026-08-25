-- Migración 0010: nombre corto (username) del profesor, usado en las
-- secciones públicas donde no cabe "nombre apellidos" completo.
ALTER TABLE profesores
  ADD COLUMN username VARCHAR(60) DEFAULT NULL AFTER apellidos;
