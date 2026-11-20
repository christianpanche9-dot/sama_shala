-- Migración 0030: agrega "efectivo" como método de pago de un paquete,
-- para que el admin pueda activar desde el panel un paquete que el cliente
-- ya pagó en persona (sin pasar por transferencia ni PayPhone).

ALTER TABLE paquetes_clientes
  MODIFY COLUMN metodo_pago ENUM('simulado','transferencia','payphone','efectivo') NOT NULL DEFAULT 'payphone';
