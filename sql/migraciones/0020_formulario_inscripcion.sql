-- Migración 0020: formulario de inscripción propio (salud, experiencia
-- previa y autorización de datos/imagen), en sustitución del formulario
-- externo de Google. Un registro por usuario; si vuelve a enviarlo se
-- actualiza el mismo registro.

CREATE TABLE formulario_inscripcion (
  id_inscripcion INT(11) NOT NULL AUTO_INCREMENT,
  id_usuario INT(11) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  telefono VARCHAR(30) NOT NULL,
  fecha_nacimiento DATE NOT NULL,
  experiencia_previa ENUM('si','no') NOT NULL,
  tiene_lesion ENUM('si','no') NOT NULL,
  detalle_lesion TEXT NULL,
  tiene_cirugia ENUM('si','no') NOT NULL,
  detalle_cirugia TEXT NULL,
  hobbies TEXT NULL,
  autorizacion_datos_imagen ENUM('si','no') NOT NULL,
  fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_inscripcion),
  UNIQUE KEY id_usuario (id_usuario),
  CONSTRAINT fk_formulario_inscripcion_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
