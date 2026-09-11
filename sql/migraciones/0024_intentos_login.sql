-- Migración 0024: límite de intentos de inicio de sesión.
-- Registra cada intento fallido de login (email + IP) para poder bloquear
-- temporalmente a quien esté probando contraseñas por fuerza bruta.
-- validar_login.php borra las filas de un email en cuanto inicia sesión
-- correctamente; las filas viejas no se limpian solas, así que conviene
-- purgar de vez en cuando las de más de un día (no es obligatorio, la
-- consulta de conteo solo mira los últimos 15 minutos).

CREATE TABLE intentos_login (
  id_intento INT(11) NOT NULL AUTO_INCREMENT,
  email VARCHAR(150) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_intento),
  KEY idx_intentos_login_email (email, creado_en),
  KEY idx_intentos_login_ip (ip, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
