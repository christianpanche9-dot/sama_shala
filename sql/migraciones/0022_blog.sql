-- Migración 0022: sección de Blog. El admin publica entradas con portada,
-- contenido enriquecido (texto, imágenes, video de YouTube embebido) y los
-- visitantes las ven en una página pública de listado + detalle.

CREATE TABLE blog_entradas (
  id_entrada INT(11) NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(200) NOT NULL,
  portada VARCHAR(255) NOT NULL,
  contenido LONGTEXT NOT NULL,
  video_url VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_entrada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
