-- Migración 0021: agrega el pago por transferencia bancaria como alternativa
-- al pago simulado. El cliente adjunta una foto del comprobante y la compra
-- queda pendiente de revisión hasta que el admin la aprueba.

ALTER TABLE paquetes_clientes
  MODIFY COLUMN estado ENUM('pendiente','activo','agotado','caducado','cancelado') NOT NULL DEFAULT 'activo',
  MODIFY COLUMN metodo_pago ENUM('simulado','transferencia') NOT NULL DEFAULT 'simulado',
  ADD COLUMN comprobante_pago VARCHAR(255) DEFAULT NULL AFTER referencia_pago;

ALTER TABLE reservas
  MODIFY COLUMN metodo_pago ENUM('simulado','transferencia') DEFAULT NULL,
  ADD COLUMN estado_pago ENUM('pagado','pendiente') NOT NULL DEFAULT 'pagado' AFTER estado,
  ADD COLUMN comprobante_pago VARCHAR(255) DEFAULT NULL AFTER referencia_pago;

ALTER TABLE compras_productos
  ADD COLUMN comprobante_pago VARCHAR(255) DEFAULT NULL AFTER referencia_pago;
