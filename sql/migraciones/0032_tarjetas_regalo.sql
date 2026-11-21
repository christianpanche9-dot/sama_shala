-- Migración 0032: tarjetas de regalo. Son un saldo en dólares (no un vale
-- fijo para un ítem exacto): se compran eligiendo un producto, paquete,
-- evento o terapia solo para fijar un monto "bonito", pero el código
-- resultante se puede canjear contra cualquier compra del sitio (tienda,
-- paquetes, reservas de evento/terapia/taller o clase suelta de invitado).
-- Si el saldo no alcanza para cubrir la compra, se cobra la diferencia con
-- PayPhone; si sobra, el resto queda disponible para una próxima compra.

CREATE TABLE tarjetas_regalo (
  id_tarjeta_regalo INT(11) NOT NULL AUTO_INCREMENT,
  id_usuario INT(11) NOT NULL COMMENT 'quien compro la tarjeta',
  codigo VARCHAR(20) NOT NULL COMMENT 'codigo que se canjea, distinto de referencia_pago',
  categoria_origen ENUM('tienda','paquetes','eventos','terapias') NOT NULL,
  descripcion_origen VARCHAR(150) NOT NULL COMMENT 'para mostrar en la tarjeta, no restringe el canje',
  saldo_inicial DECIMAL(10,2) NOT NULL,
  saldo_disponible DECIMAL(10,2) NOT NULL,
  metodo_pago ENUM('payphone') NOT NULL DEFAULT 'payphone',
  referencia_pago VARCHAR(40) DEFAULT NULL,
  estado ENUM('pendiente','activa','agotada','caducada','cancelada') NOT NULL DEFAULT 'pendiente',
  fecha_compra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_activacion DATETIME DEFAULT NULL,
  fecha_caducidad DATE DEFAULT NULL,
  PRIMARY KEY (id_tarjeta_regalo),
  UNIQUE KEY codigo (codigo),
  UNIQUE KEY referencia_pago (referencia_pago),
  KEY fk_tarjeta_regalo_usuario (id_usuario),
  CONSTRAINT fk_tarjeta_regalo_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Las tablas de compras necesitan registrar cuanto de cada compra se pago
-- con saldo de tarjeta de regalo (puede ser una cobertura parcial, con el
-- resto cobrado por PayPhone), ademas de poder usarla como metodo_pago
-- cuando cubre el 100% del precio.

ALTER TABLE reservas
  MODIFY COLUMN metodo_pago ENUM('simulado','transferencia','payphone','efectivo','tarjeta_regalo') DEFAULT NULL,
  ADD COLUMN monto_tarjeta_regalo DECIMAL(10,2) DEFAULT NULL AFTER id_transaccion_payphone,
  ADD COLUMN id_tarjeta_regalo_usada INT(11) DEFAULT NULL AFTER monto_tarjeta_regalo,
  ADD CONSTRAINT fk_reserva_tarjeta_regalo FOREIGN KEY (id_tarjeta_regalo_usada) REFERENCES tarjetas_regalo (id_tarjeta_regalo);

ALTER TABLE paquetes_clientes
  MODIFY COLUMN metodo_pago ENUM('simulado','transferencia','payphone','efectivo','tarjeta_regalo') NOT NULL DEFAULT 'payphone',
  ADD COLUMN monto_tarjeta_regalo DECIMAL(10,2) DEFAULT NULL AFTER id_transaccion_payphone,
  ADD COLUMN id_tarjeta_regalo_usada INT(11) DEFAULT NULL AFTER monto_tarjeta_regalo,
  ADD CONSTRAINT fk_paquete_cliente_tarjeta_regalo FOREIGN KEY (id_tarjeta_regalo_usada) REFERENCES tarjetas_regalo (id_tarjeta_regalo);

ALTER TABLE compras_productos
  ADD COLUMN monto_tarjeta_regalo DECIMAL(10,2) DEFAULT NULL AFTER id_transaccion_payphone,
  ADD COLUMN id_tarjeta_regalo_usada INT(11) DEFAULT NULL AFTER monto_tarjeta_regalo,
  ADD CONSTRAINT fk_compra_producto_tarjeta_regalo FOREIGN KEY (id_tarjeta_regalo_usada) REFERENCES tarjetas_regalo (id_tarjeta_regalo);
