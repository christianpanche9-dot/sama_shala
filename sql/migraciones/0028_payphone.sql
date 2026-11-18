-- Migración 0028: agrega PayPhone como método de pago real (sustituye al
-- "pago simulado" en los formularios). Se mantiene 'simulado' en los ENUM
-- por compatibilidad con compras históricas; solo se deja de ofrecer en
-- los formularios. Guardamos el id de transacción de PayPhone para poder
-- auditar/soportar cobros desde el panel de admin.

ALTER TABLE paquetes_clientes
  MODIFY COLUMN metodo_pago ENUM('simulado','transferencia','payphone') NOT NULL DEFAULT 'payphone',
  ADD COLUMN id_transaccion_payphone VARCHAR(40) DEFAULT NULL AFTER comprobante_pago;

ALTER TABLE reservas
  MODIFY COLUMN metodo_pago ENUM('simulado','transferencia','payphone') DEFAULT NULL,
  ADD COLUMN id_transaccion_payphone VARCHAR(40) DEFAULT NULL AFTER comprobante_pago;

ALTER TABLE compras_productos
  ADD COLUMN id_transaccion_payphone VARCHAR(40) DEFAULT NULL AFTER comprobante_pago;
