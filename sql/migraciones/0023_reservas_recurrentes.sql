-- Migración 0023: reservas recurrentes por horario fijo.
-- Permite a un cliente inscribirse a una clase que se repite cada semana
-- (misma actividad, mismo día de la semana, misma hora) en vez de reservar
-- sesión por sesión. Solo cubre sesiones que el admin ya haya cargado al
-- momento de inscribirse (no hay generador automático de sesiones futuras).
-- Si el paquete del cliente se acaba antes de cubrir todas las sesiones
-- futuras del patrón, las que sobran quedan en estado 'pre_reserva' (no
-- gastan cupo de paquete ni cuentan para el aforo) y se activan solas,
-- en orden cronológico, cuando el cliente compre un paquete nuevo.

CREATE TABLE reservas_recurrentes (
  id_recurrente INT(11) NOT NULL AUTO_INCREMENT,
  id_usuario INT(11) NOT NULL,
  id_actividad INT(11) NOT NULL,
  dia_semana TINYINT(1) NOT NULL COMMENT '1=lunes .. 7=domingo (ISO-8601, DateTime::format(N))',
  hora_inicio TIME NOT NULL,
  estado ENUM('activa','cancelada') NOT NULL DEFAULT 'activa',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_recurrente),
  UNIQUE KEY uq_usuario_patron (id_usuario, id_actividad, dia_semana, hora_inicio),
  CONSTRAINT fk_reservas_recurrentes_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
    ON DELETE CASCADE,
  CONSTRAINT fk_reservas_recurrentes_actividad
    FOREIGN KEY (id_actividad) REFERENCES actividades (id_actividad)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE reservas
  ADD COLUMN id_recurrente INT(11) NULL AFTER id_paquete_cliente,
  ADD CONSTRAINT fk_reserva_recurrente
    FOREIGN KEY (id_recurrente) REFERENCES reservas_recurrentes (id_recurrente)
    ON DELETE SET NULL;

ALTER TABLE reservas
  MODIFY COLUMN estado ENUM('confirmada', 'pre_reserva', 'cancelada') NOT NULL DEFAULT 'confirmada';
