-- Migración 0002: renombrar el concepto "bono" a "paquete" y "monitor" a "profesor"
-- en toda la base de datos. Requiere que la migración 0001 ya esté aplicada.
-- Nota: cada ALTER/RENAME hace commit implícito en MySQL/MariaDB, así que este script
-- no es atómico; si falla a mitad, revisa qué paso se aplicó antes de reintentar.

-- 1. Quitar las claves foráneas que dependen de las tablas/columnas a renombrar.
ALTER TABLE bonos_clientes DROP FOREIGN KEY bonos_clientes_ibfk_1;
ALTER TABLE bonos_clientes DROP FOREIGN KEY bonos_clientes_ibfk_2;
ALTER TABLE reservas DROP FOREIGN KEY fk_reserva_bono_cliente;
ALTER TABLE sesiones DROP FOREIGN KEY fk_sesion_monitor;
ALTER TABLE tipos_bono DROP FOREIGN KEY fk_tipo_bono_tenant;
ALTER TABLE monitores DROP FOREIGN KEY fk_monitor_tenant;

-- 2. Renombrar tablas.
RENAME TABLE tipos_bono TO tipos_paquete;
RENAME TABLE bonos_clientes TO paquetes_clientes;
RENAME TABLE monitores TO profesores;

-- 3. Renombrar columnas.
ALTER TABLE tipos_paquete
  CHANGE COLUMN id_tipo_bono id_tipo_paquete INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE paquetes_clientes
  CHANGE COLUMN id_bono_cliente id_paquete_cliente INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE COLUMN id_tipo_bono id_tipo_paquete INT(11) NOT NULL;

ALTER TABLE paquetes_clientes DROP INDEX id_tipo_bono, ADD INDEX id_tipo_paquete (id_tipo_paquete);

ALTER TABLE profesores
  CHANGE COLUMN id_monitor id_profesor INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE sesiones
  CHANGE COLUMN id_monitor id_profesor INT(11) NOT NULL;

ALTER TABLE sesiones DROP INDEX idx_sesion_monitor_fecha, ADD INDEX idx_sesion_profesor_fecha (id_profesor, fecha);
ALTER TABLE sesiones DROP INDEX idx_sesiones_monitor_horario, ADD INDEX idx_sesiones_profesor_horario (id_profesor, fecha, hora_inicio, hora_fin);

ALTER TABLE reservas
  CHANGE COLUMN id_bono_cliente id_paquete_cliente INT DEFAULT NULL;

-- 4. Migrar el valor del enum tipo_pago ('bono' -> 'paquete') sin perder datos existentes.
ALTER TABLE reservas
  MODIFY COLUMN tipo_pago ENUM('bono','paquete','suelta') NOT NULL DEFAULT 'suelta';
UPDATE reservas SET tipo_pago = 'paquete' WHERE tipo_pago = 'bono';
ALTER TABLE reservas
  MODIFY COLUMN tipo_pago ENUM('paquete','suelta') NOT NULL DEFAULT 'suelta';

-- 5. Volver a crear las claves foráneas con nombres consistentes.
ALTER TABLE paquetes_clientes
  ADD CONSTRAINT paquetes_clientes_ibfk_1 FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
  ADD CONSTRAINT paquetes_clientes_ibfk_2 FOREIGN KEY (id_tipo_paquete) REFERENCES tipos_paquete (id_tipo_paquete);

ALTER TABLE reservas
  ADD CONSTRAINT fk_reserva_paquete_cliente FOREIGN KEY (id_paquete_cliente) REFERENCES paquetes_clientes (id_paquete_cliente);

ALTER TABLE sesiones
  ADD CONSTRAINT fk_sesion_profesor FOREIGN KEY (id_profesor) REFERENCES profesores (id_profesor);

ALTER TABLE tipos_paquete
  ADD CONSTRAINT fk_tipo_paquete_tenant FOREIGN KEY (id_tenant) REFERENCES tenants (id_tenant);

ALTER TABLE profesores
  ADD CONSTRAINT fk_profesor_tenant FOREIGN KEY (id_tenant) REFERENCES tenants (id_tenant);
