-- Migración 0001: preparar la BD para SaaS (multi-tenant futuro) y activar bonos + pagos simulados.
-- Reutiliza las tablas `tipos_bono` y `bonos_clientes` que ya trae el esquema base.
-- Nota: cada ALTER TABLE hace commit implícito en MySQL/MariaDB, así que este script
-- no es atómico; si falla a mitad, revisa qué ALTER se aplicó antes de reintentar.

-- 1. Tenants: un solo registro por ahora (Sama Shala). id_tenant en las tablas clave
--    queda listo para dar de alta más escuelas en el futuro sin rediseñar el esquema.
CREATE TABLE IF NOT EXISTS tenants (
  id_tenant INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  slug VARCHAR(80) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_tenant),
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tenants (id_tenant, nombre, slug)
VALUES (1, 'Sama Shala', 'sama-shala');

-- 2. id_tenant en las tablas que hoy son de un solo estudio.
ALTER TABLE usuarios
  ADD COLUMN id_tenant INT NOT NULL DEFAULT 1 AFTER id_usuario,
  ADD KEY fk_usuario_tenant (id_tenant),
  ADD CONSTRAINT fk_usuario_tenant FOREIGN KEY (id_tenant) REFERENCES tenants (id_tenant);

ALTER TABLE actividades
  ADD COLUMN id_tenant INT NOT NULL DEFAULT 1 AFTER id_actividad,
  ADD KEY fk_actividad_tenant (id_tenant),
  ADD CONSTRAINT fk_actividad_tenant FOREIGN KEY (id_tenant) REFERENCES tenants (id_tenant);

ALTER TABLE espacios
  ADD COLUMN id_tenant INT NOT NULL DEFAULT 1 AFTER id_espacio,
  ADD KEY fk_espacio_tenant (id_tenant),
  ADD CONSTRAINT fk_espacio_tenant FOREIGN KEY (id_tenant) REFERENCES tenants (id_tenant);

ALTER TABLE monitores
  ADD COLUMN id_tenant INT NOT NULL DEFAULT 1 AFTER id_monitor,
  ADD KEY fk_monitor_tenant (id_tenant),
  ADD CONSTRAINT fk_monitor_tenant FOREIGN KEY (id_tenant) REFERENCES tenants (id_tenant);

ALTER TABLE sesiones
  ADD COLUMN id_tenant INT NOT NULL DEFAULT 1 AFTER id_sesion,
  ADD KEY fk_sesion_tenant (id_tenant),
  ADD CONSTRAINT fk_sesion_tenant FOREIGN KEY (id_tenant) REFERENCES tenants (id_tenant);

ALTER TABLE tipos_bono
  ADD COLUMN id_tenant INT NOT NULL DEFAULT 1 AFTER id_tipo_bono,
  ADD KEY fk_tipo_bono_tenant (id_tenant),
  ADD CONSTRAINT fk_tipo_bono_tenant FOREIGN KEY (id_tenant) REFERENCES tenants (id_tenant);

-- 3. Datos de pago simulado directamente en bonos_clientes (evita una tabla `pagos`
--    separada para un único método de pago simulado).
ALTER TABLE bonos_clientes
  ADD COLUMN precio_pagado DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER usos_disponibles,
  ADD COLUMN metodo_pago ENUM('simulado') NOT NULL DEFAULT 'simulado' AFTER precio_pagado,
  ADD COLUMN referencia_pago VARCHAR(40) DEFAULT NULL AFTER metodo_pago,
  ADD UNIQUE KEY referencia_pago (referencia_pago);

-- 4. Vincular una reserva con el bono usado para pagarla, para poder revertir el
--    consumo si la reserva se cancela (mismo espíritu que la reversión de aforo que
--    ya hace cancelarReservaYPromocionar).
ALTER TABLE reservas
  ADD COLUMN id_bono_cliente INT DEFAULT NULL AFTER id_usuario,
  ADD COLUMN tipo_pago ENUM('bono','suelta') NOT NULL DEFAULT 'suelta' AFTER id_bono_cliente,
  ADD KEY fk_reserva_bono_cliente (id_bono_cliente),
  ADD CONSTRAINT fk_reserva_bono_cliente FOREIGN KEY (id_bono_cliente) REFERENCES bonos_clientes (id_bono_cliente);
