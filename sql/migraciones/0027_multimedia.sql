-- Migración 0027: sección de Multimedia. El admin publica fichas con
-- título, una descripción breve y enlaces (embebidos) al material de
-- Sama Shala en Spotify y/o YouTube. Los visitantes las ven en una
-- página pública de listado.

CREATE TABLE multimedia (
  id_multimedia INT(11) NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(200) NOT NULL,
  descripcion VARCHAR(300) NOT NULL,
  url_youtube VARCHAR(255) NULL,
  url_spotify VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_multimedia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
