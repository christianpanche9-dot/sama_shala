-- Migración 0016: permite asignar uno o más profesores a una sesión.
-- sesiones.id_profesor se conserva como "profesor principal" (el primero
-- elegido) para no romper índices ni consultas existentes; la lista
-- completa de profesores de cada sesión vive en sesiones_profesores.

CREATE TABLE sesiones_profesores (
  id_sesion INT(11) NOT NULL,
  id_profesor INT(11) NOT NULL,
  PRIMARY KEY (id_sesion, id_profesor),
  KEY fk_sp_profesor (id_profesor),
  CONSTRAINT fk_sp_sesion FOREIGN KEY (id_sesion) REFERENCES sesiones (id_sesion) ON DELETE CASCADE,
  CONSTRAINT fk_sp_profesor FOREIGN KEY (id_profesor) REFERENCES profesores (id_profesor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migra los datos existentes: el profesor actual de cada sesión pasa a ser
-- su único profesor en la nueva tabla.
INSERT INTO sesiones_profesores (id_sesion, id_profesor)
SELECT id_sesion, id_profesor FROM sesiones;
